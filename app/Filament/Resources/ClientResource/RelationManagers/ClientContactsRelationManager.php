<?php

namespace App\Filament\Resources\ClientResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;

class ClientContactsRelationManager extends RelationManager
{
    protected static string $relationship = 'contacts';
    protected static ?string $recordTitleAttribute = 'contact_name';

    protected static bool $canCreate = true;
    public function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            TextInput::make('contact_name')
                ->label('اسم جهة الاتصال')
                ->required()
                ->maxLength(100),

            TextInput::make('position')
                ->label('المسمى الوظيفي')
                ->maxLength(100),

            TextInput::make('email')
                ->label('البريد الإلكتروني')
                ->email()
                ->maxLength(100),

            TextInput::make('phone')
                ->label('رقم الجوال')
                ->tel()
                ->maxLength(20),

            Toggle::make('is_primary')
                ->label('جهة الاتصال الرئيسية'),

            Textarea::make('notes')
                ->label('ملاحظات')
                ->maxLength(500),
        ]);
    }

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('contact_name')->label('الاسم')->searchable()->sortable(),
                TextColumn::make('position')->label('الوظيفة')->sortable(),
                TextColumn::make('email')->label('البريد الإلكتروني')->copyable(),
                TextColumn::make('phone')->label('الجوال')->copyable(),
                IconColumn::make('is_primary')->label('رئيسي')->boolean(),
            ])
            ->defaultSort('contact_name')
            ->actions([
                EditAction::make()->label('تعديل'),
                DeleteAction::make()->label('حذف'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('إضافة جهة اتصال جديدة'),
            ])
            ->emptyStateHeading('لا توجد جهات اتصال')
            ->emptyStateDescription('لم يتم تسجيل أي جهة اتصال لهذا العميل.');
    }
}
