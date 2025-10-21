<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmployeeResource\Pages;
use App\Filament\Resources\EmployeeResource\Pages\CreateEmployee;
use App\Filament\Resources\EmployeeResource\Pages\EditEmployee;
use App\Filament\Resources\EmployeeResource\Pages\ListEmployees;
use App\Models\Employee;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\TrashedFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Models\Role;

// Table actions (SoftDeletes)
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\ForceDeleteAction;
use Filament\Tables\Actions\RestoreBulkAction;
use Filament\Tables\Actions\ForceDeleteBulkAction;

class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'الموظفين';
    protected static ?string $modelLabel = 'موظف';
    protected static ?string $pluralModelLabel = 'الموظفين';
    protected static ?string $recordTitleAttribute = 'employee_name';
    protected static bool $shouldRegisterNavigation = false;

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
                            ->statePath('user.email')
                            ->formatStateUsing(fn ($state, ?\App\Models\Employee $record) =>
                                $state ?? ($record?->user?->email)
                            )
                            ->unique(
                                table: 'users',
                                column: 'email',
                                ignorable: fn ($record) => $record?->user   // تجاهل مستخدم السجل الحالي
                            ),

                        Forms\Components\TextInput::make('user.password')
                            ->label('كلمة المرور')
                            ->password()
                            ->formatStateUsing(fn () => '')
                            ->dehydrated(fn ($state) => filled($state))
                            ->dehydrateStateUsing(fn ($state) => \Illuminate\Support\Facades\Hash::make($state))
                            ->required(fn (string $operation) => $operation === 'create')
                            ->autocomplete('new-password')
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
                            ->relationship('department', 'dept_name')
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

                // -- الأدوار (على User فقط) --
                Forms\Components\Section::make('مجموعات المستخدمين والصلاحيات')
                    ->schema([
                        Select::make('roles_ids')
                            ->label('الأدوار (تُطبّق على حساب المستخدم)')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->options(fn () => Role::where('guard_name', 'web')
                                ->orderBy('name')
                                ->pluck('name', 'id')
                                ->all()
                            )
                            ->afterStateHydrated(function (Select $component, $state, ?Employee $record) {
                                $ids = $record?->user?->roles()
                                    ->pluck('id')
                                    ->all() ?? [];
                                $component->state($ids);
                            })
                            ->dehydrated(false) // لا تُحفظ مباشرة على employees
                            ->helperText('الأدوار تُسند إلى المستخدم المرتبط بالموظف.'),
                    ])->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('employee_id', 'desc')
            ->paginated([10, 25, 50])
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

                // نعرض أدوار المستخدم المرتبط
                Tables\Columns\TagsColumn::make('user.roles.name')
                    ->label('الأدوار'),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('نشط'),

                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime('Y-m-d H:i')
                    ->label('محذوف؟')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('الحالة (نشط)'),

                Tables\Filters\SelectFilter::make('department')
                    ->relationship('department', 'dept_name')
                    ->label('القسم'),

                TrashedFilter::make()
                    ->label('المحذوفات'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn (Employee $record) => is_null($record->deleted_at)),
                RestoreAction::make()
                    ->visible(fn (Employee $record) => ! is_null($record->deleted_at)),
                ForceDeleteAction::make()
                    ->visible(fn (Employee $record) => ! is_null($record->deleted_at)),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListEmployees::route('/'),
            'create' => CreateEmployee::route('/create'),
            'edit'   => EditEmployee::route('/{record}/edit'),
        ];
    }


    public static function syncUserAndRoles(Employee $record, array $data): void
    {
        DB::transaction(function () use ($record, $data) {
            if (isset($data['user'])) {
                $userData = $data['user'];

                if ($record->user) {
                    $record->user->update(array_filter($userData));
                } else {
                    /** @var User $user */
                    $user = User::create($userData);
                    $record->user()->associate($user)->save();
                }
            }

            if (isset($data['roles_ids'])) {
                $roleNames = Role::where('guard_name', 'web')
                    ->whereIn('id', (array) $data['roles_ids'])
                    ->pluck('name')
                    ->all();

                if ($record->user) {
                    $record->user->syncRoles($roleNames);
                }
            }

            DB::table(config('permission.table_names.model_has_roles', 'model_has_roles'))
                ->where('model_type', Employee::class)
                ->where('model_id', $record->getKey())
                ->delete();

            app(PermissionRegistrar::class)->forgetCachedPermissions();
        });
    }
}
