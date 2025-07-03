<?php

namespace App\Filament\Resources;

use App\Models\Employee;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\EmployeeResource\Pages;

class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $modelLabel = 'Employee';

    protected static ?string $navigationLabel = 'Employees';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Personal Information')
                    ->schema([
                        Forms\Components\TextInput::make('employee_name')
                            ->required()
                            ->maxLength(255),
                            
                        Forms\Components\TextInput::make('national_id')
                            ->label('National ID')
                            ->maxLength(50),
                            
                        Forms\Components\Select::make('gender')
                            ->options([
                                'male' => 'Male',
                                'female' => 'Female',
                            ]),
                            
                        Forms\Components\DatePicker::make('birth_date'),
                    ])->columns(2),
                    
                Forms\Components\Section::make('Contact Information')
                    ->schema([
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->maxLength(100),
                            
                        Forms\Components\TextInput::make('phone')
                            ->tel()
                            ->maxLength(20),
                            
                        Forms\Components\Textarea::make('address')
                            ->columnSpanFull(),
                    ])->columns(2),
                    
                Forms\Components\Section::make('Employment Details')
                    ->schema([
                        Forms\Components\TextInput::make('position')
                            ->required()
                            ->maxLength(100),
                            
                        Forms\Components\DatePicker::make('hire_date')
                            ->required(),
                            
                        Forms\Components\TextInput::make('salary')
                            ->numeric()
                            ->prefix('$'),
                            
                        Forms\Components\Select::make('employment_type')
                            ->options([
                                'full_time' => 'Full Time',
                                'part_time' => 'Part Time',
                                'contract' => 'Contract',
                            ]),
                            
                        Forms\Components\Toggle::make('is_active')
                            ->default(true),
                    ])->columns(2),
                    
                Forms\Components\Section::make('Emergency Contact')
                    ->schema([
                        Forms\Components\TextInput::make('emergency_contact_name')
                            ->maxLength(100),
                            
                        Forms\Components\TextInput::make('emergency_contact_phone')
                            ->maxLength(20),
                    ])->columns(2),
                    
                Forms\Components\Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employee_name')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('position')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('department_id')
                    ->label('Department ID')
                    ->numeric(),
                    
                Tables\Columns\TextColumn::make('hire_date')
                    ->date()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('salary')
                    ->money('USD')
                    ->sortable(),
                    
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('gender')
                    ->options([
                        'male' => 'Male',
                        'female' => 'Female',
                    ]),
                    
                Tables\Filters\SelectFilter::make('employment_type')
                    ->options([
                        'full_time' => 'Full Time',
                        'part_time' => 'Part Time',
                        'contract' => 'Contract',
                    ]),
                    
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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