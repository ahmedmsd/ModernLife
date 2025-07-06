<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DepartmentResource\Pages;
use App\Models\Department;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Resources\Resource;


class DepartmentResource extends Resource
{
    protected static ?string $model = Department::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('dept_name')
                    ->label('اسم القسم')
                    ->required()
                    ->maxLength(100),

                Forms\Components\TextInput::make('dept_code')
                    ->label('كود القسم')
                    ->maxLength(20),


                Forms\Components\Select::make('dept_type')
                    ->label('نوع القسم')
                    ->relationship('category', 'category_name')
                    ->required(),

                Forms\Components\Select::make('parent_dept_id')
                    ->label('القسم التابع له')
                    ->relationship('parentDepartment', 'dept_name')
                    ->nullable(),


                Forms\Components\Select::make('manager_id')
                    ->label('مدير القسم')
                    ->nullable(),

                Forms\Components\TextInput::make('location')
                    ->label('الموقع')
                    ->maxLength(100),

                Forms\Components\TextInput::make('phone_extension')
                    ->label('تحويلة الهاتف')
                    ->maxLength(10),

                Forms\Components\TextInput::make('email')
                    ->label('البريد الإلكتروني')
                    ->email()
                    ->nullable(),

                Forms\Components\Toggle::make('is_active')
                    ->label('فعال')
                    ->default(true),

                Forms\Components\ColorPicker::make('color_code')
                    ->label('اللون المميز')
                    ->default('#3498db'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('dept_name')->label('اسم القسم')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('dept_code')->label('الكود')->sortable(),
                Tables\Columns\TextColumn::make('category.category_name')->label('نوع القسم')->sortable()->searchable(),

                Tables\Columns\TextColumn::make('parentDepartment.dept_name')->label('القسم التابع له')->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('الحالة')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // DepartmentManagersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDepartments::route('/'),
            'create' => Pages\CreateDepartment::route('/create'),
            'edit' => Pages\EditDepartment::route('/{record}/edit'),
        ];
    }
}
