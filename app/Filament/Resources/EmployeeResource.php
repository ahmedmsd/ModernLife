<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmployeeResource\Pages;
use App\Models\Employee;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Hash;

class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'الموظفين';
    protected static ?string $modelLabel = 'موظف';
    protected static ?string $pluralModelLabel = 'الموظفين';
    protected static ?string $recordTitleAttribute = 'employee_name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('معلومات الحساب')
                    ->schema([
                        Forms\Components\TextInput::make('user.email')
                            ->label('البريد الإلكتروني (للدخول)')
                            ->email()
                            ->required()
                            ->default(fn($record) => $record?->user?->email)
                            ->statePath('user.email')
                            ->unique(
                                table: 'users',
                                column: 'email',
                                ignorable: fn($record) => $record?->user,
                            ),
                        Forms\Components\TextInput::make('user.password')
                            ->label('كلمة المرور')
                            ->password()
                            ->required()
                            ->statePath('user.password')
                            ->dehydrated(fn($state) => filled($state))
                            ->dehydrateStateUsing(fn($state) => Hash::make($state))
                            ->required(fn(string $operation): bool => $operation === 'create')
                            ->autocomplete('new-password'),
                    ])->columns(2),

                Forms\Components\Section::make('البيانات الأساسية للموظف')
                    ->schema([
                        Forms\Components\TextInput::make('employee_name')
                            ->required()
                            ->maxLength(255)
                            ->label('الاسم الكامل للموظف'),
                        Forms\Components\TextInput::make('national_id')
                            ->maxLength(20)
                            ->required()
                            ->label('الرقم الوطني / الهوية'),
                        Forms\Components\DatePicker::make('birth_date')
                            ->label('تاريخ الميلاد'),
                        Forms\Components\Select::make('gender')
                            ->options([
                                'male' => 'ذكر',
                                'female' => 'أنثى',
                            ])
                            ->label('الجنس'),
                    ])->columns(2),

                Forms\Components\Section::make('معلومات الاتصال')
                    ->schema([
                        Forms\Components\TextInput::make('phone')
                            ->tel()
                            ->required()
                            ->maxLength(20)
                            ->label('رقم الجوال'),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->maxLength(255)
                            ->label('البريد الإلكتروني (للتواصل)'),
                        Forms\Components\Textarea::make('address')
                            ->columnSpanFull()
                            ->label('العنوان'),
                    ])->columns(2),

                Forms\Components\Section::make('معلومات الوظيفة')
                    ->schema([
                        Forms\Components\TextInput::make('position')
                            ->required()
                            ->maxLength(255)
                            ->label('المنصب الوظيفي'),
                        Forms\Components\Select::make('department_id')
                            ->label('القسم التابع له')
                            ->relationship('department', 'dept_name') // Changed to lowercase
                            ->required(),
                        Forms\Components\DatePicker::make('hire_date')
                            ->label('تاريخ التعيين'),
                        Forms\Components\TextInput::make('salary')
                            ->numeric()
                            ->prefix('SAR')
                            ->label('الراتب'),
                        Forms\Components\Select::make('employment_type')
                            ->options([
                                'full_time' => 'دوام كامل',
                                'part_time' => 'دوام جزئي',
                                'contractor' => 'متعاقد',
                            ])
                            ->default('full_time')
                            ->label('نوع التوظيف'),
                        Forms\Components\Toggle::make('is_active')
                            ->required()
                            ->default(true)
                            ->label('حالة الحساب (نشط)'),
                    ])->columns(2),

                Forms\Components\Section::make('مجموعات المستخدمين والصلاحيات')
                    ->schema([
                        Forms\Components\Select::make('roles')
                            ->multiple()
                            ->relationship('roles', 'name')
                            ->preload()
                            ->required()
                            ->validationMessages([
                                'required' => 'يجب اختيار دور واحد على الأقل'
                            ])
                            ->label('الأدوار')
                            ->helperText('يتم توريث جميع صلاحيات الدور للموظف'),
                    ])->columns(1) // Changed to single column for better layout
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employee_name')
                    ->searchable()
                    ->sortable()
                    ->label('اسم الموظف'),
                Tables\Columns\TextColumn::make('user.email')
                    ->label('البريد الإلكتروني للحساب')
                    ->searchable(),
                Tables\Columns\TextColumn::make('department.dept_name')
                    ->label('القسم')
                    ->searchable(),
                Tables\Columns\TextColumn::make('position')
                    ->label('المنصب')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('رقم الجوال')
                    ->searchable(),
                Tables\Columns\TextColumn::make('roles.name')
                    ->label('الدور')
                    ->badge()
                    ->color('primary'),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('نشط'),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('الحالة (نشط)'),
                Tables\Filters\SelectFilter::make('department')
                    ->relationship('department', 'dept_name')
                    ->label('القسم'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function afterSave($record, $data)
    {
        if (isset($data['roles'])) {
            $record->syncRoles($data['roles']); // Sync roles via Spatie
        }

        // Handle user account creation/update
        if (isset($data['user'])) {
            $userData = $data['user'];
            if ($record->user) {
                $record->user->update($userData);
            } else {
                $user = \App\Models\User::create($userData);
                $record->user()->associate($user)->save();
            }
        }
    }
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmployees::route('/'),
            'create' => Pages\CreateEmployee::route('/create'),
            'edit' => Pages\EditEmployee::route('/{record}/edit'),
        ];
    }
}
