<?php

namespace App\Filament\Resources\TaskResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Forms;
use Filament\Forms\Components\{Textarea, FileUpload};
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class CommentsRelationManager extends RelationManager
{
    protected static string $relationship = 'comments';
    protected static ?string $title = 'التعليقات';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return true;
    }

    public function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Textarea::make('body')->label('نص التعليق')->required()->autosize()->maxLength(5000),
            FileUpload::make('attachments')->label('مرفقات (اختياري)')
                ->multiple()->directory('task-comments')
                ->downloadable()->openable(),
        ]);
    }

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $q) {
                $q->where('task_id', $this->ownerRecord->getKey())
                    ->orderByDesc('id');
            })
            ->columns([
                TextColumn::make('author.name')->label('المعلّق')->badge(),
                TextColumn::make('body')->label('التعليق')->wrap(),
                TextColumn::make('created_at')->label('التاريخ')->since(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('إضافة تعليق')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['user_id'] = auth()->id();
                        if (!empty($data['attachments'])) {
                            $data['attachments'] = array_values((array) $data['attachments']);
                        }
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label('عرض'),
                Tables\Actions\EditAction::make()->label('تعديل'),
                Tables\Actions\DeleteAction::make()->label('حذف'),
            ]);
    }
}
