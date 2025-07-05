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
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Facades\Hash;
use Psy\TabCompletion\AutoCompleter;

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
                Section::make('معلومات الحساب')
                    ->schema([
                        TextInput::make('user.email') // <-- حقل البريد الإلكتروني للمستخدم
                            ->label('البريد الإلكتروني (للدخول)')
                            ->email()
                            ->required()
                            ->default(fn($record) => $record->user?->email)
                            ->statePath('user.email')
                            ->autocomplete('off')
                            ->unique(
                                table: 'users',
                                column: 'email',
                                ignorable: fn($record) => $record?->user,
                            ),
                        TextInput::make('user.password') // <-- حقل كلمة المرور
                            ->label('كلمة المرور (اتركه فارغًا إن لم ترغب بالتغيير)')
                            ->password()
                            ->statePath('user.password')
                            ->dehydrated(fn($state) => filled($state))
                            ->dehydrateStateUsing(fn($state) => bcrypt($state))
                            ->default(null)
                            ->autocomplete('new-password')
                            ->required(fn(string $operation): bool => $operation === 'create'),
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
                            ->label('تاريخ الميلاد')
                            ->nullable(),

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
                            ->relationship('Department', 'dept_name'),


                        Forms\Components\DatePicker::make('hire_date')
                            ->label('تاريخ التعيين')
                            ->nullable(),

                        Forms\Components\TextInput::make('salary')
                            ->numeric()
                            ->prefix('SAR')
                            ->nullable()
                            ->label('الراتب'),

                        Forms\Components\Select::make('employment_type')
                            ->options([
                                'full_time' => 'دوام كامل',
                                'part_time' => 'دوام جزئي',
                                'contractor' => 'متعاقد',
                            ])->default('full_time')
                            ->label('نوع التوظيف'),

                        Forms\Components\Toggle::make('is_active')
                            ->required()
                            ->default(true)
                            ->label('حالة الحساب (نشط)'),
                    ])->columns(2),

                Forms\Components\Section::make('معلومات الطوارئ والملاحظات')
                    ->schema([
                        Forms\Components\TextInput::make('emergency_contact_name')
                            ->maxLength(255)
                            ->nullable()
                            ->label('اسم جهة اتصال الطوارئ'),

                        Forms\Components\TextInput::make('emergency_contact_phone')
                            ->tel()
                            ->maxLength(20)
                            ->nullable()
                            ->label('رقم هاتف الطوارئ'),

                        Forms\Components\Textarea::make('notes')
                            ->columnSpanFull()
                            ->nullable()
                            ->label('ملاحظات'),
                    ])->columns(2),

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

                Tables\Columns\TextColumn::make('Department.dept_name')
                    ->searchable()
                    ->label('القسم '),


                Tables\Columns\TextColumn::make('position')
                    ->searchable()
                    ->label('المنصب'),

                Tables\Columns\TextColumn::make('phone')
                    ->searchable()
                    ->label('رقم الجوال'),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('نشط'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')->label('الحالة (نشط)'),
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('user');
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
