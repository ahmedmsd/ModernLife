<?php
namespace App\Filament\Resources\DepartmentPurchaseRequestResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Forms;
use Filament\Tables;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';
    protected static ?string $title = 'الأصناف';

    public function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('item_name')->label('الصنف')->required(),
            Forms\Components\TextInput::make('quantity')->numeric()->minValue(1)->default(1)->label('الكمية')->required(),
            Forms\Components\TextInput::make('unit')->label('الوحدة')->default('pcs'),
            Forms\Components\TextInput::make('unit_price')->numeric()->prefix('SAR')->label('سعر الوحدة'),
            Forms\Components\Textarea::make('notes')->rows(2)->label('ملاحظات'),
        ])->columns(5);
    }

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('item_name')->label('الصنف')->searchable(),
                Tables\Columns\TextColumn::make('quantity')->label('الكمية'),
                Tables\Columns\TextColumn::make('unit')->label('الوحدة'),
                Tables\Columns\TextColumn::make('unit_price')->label('سعر الوحدة')->money('sar'),
                Tables\Columns\TextColumn::make('subtotal')->label('الإجمالي')
                    ->state(fn(\App\Models\DepartmentPurchaseItem $record)=> number_format(($record->quantity ?? 0) * ($record->unit_price ?? 0), 2)),
            ])
            ->headerActions([ Tables\Actions\CreateAction::make() ])
            ->actions([ Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make() ]);
    }
}

