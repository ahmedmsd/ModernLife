<?php

namespace App\Filament\Resources\MaintenanceRequestResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Illuminate\Support\Facades\Auth;

class CommentsRelationManager extends RelationManager
{
    protected static string $relationship = 'comments';
    protected static ?string $title = 'التعليقات / الملاحظات';

    public function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\Textarea::make('note')->label('ملاحظة')->rows(4)->required()->maxLength(5000),
        ]);
    }

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->recordTitleAttribute('note')
            ->columns([
                Tables\Columns\TextColumn::make('user.name')->label('بواسطة'),
                Tables\Columns\TextColumn::make('note')->label('الملاحظة')->wrap()->limit(160),
                Tables\Columns\TextColumn::make('created_at')->label('التاريخ')->dateTime('Y-m-d H:i')->since(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('إضافة ملاحظة')
                    ->mutateFormDataUsing(fn(array $data)=> $data + ['user_id'=>Auth::id()])
                    ->visible(fn()=> Auth::user()?->hasAnyRole(['factory_manager','admin','super-admin']) ?? false),
            ]);
    }
}
