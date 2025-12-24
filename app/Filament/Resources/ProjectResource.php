<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectResource\Pages;
use App\Filament\Resources\ProjectResource\Pages\CreateProject;
use App\Filament\Resources\ProjectResource\Pages\EditProject;
use App\Filament\Resources\ProjectResource\Pages\ListProjects;
use App\Filament\Resources\ProjectResource\Pages\ManageProjectTasks;
use App\Filament\Resources\ProjectResource\Pages\ViewProject;
use App\Filament\Resources\ProjectResource\RelationManagers\TasksRelationManager;
use App\Models\Project;
use App\Support\Tenancy\RoleScope;
use App\Support\Tenancy\ShowroomFilter;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Builder;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';
    protected static ?string $navigationLabel = 'المشروعات';
    protected static ?string $pluralModelLabel = 'المشروعات';
    protected static ?string $modelLabel = 'مشروع';
    protected static bool $shouldRegisterNavigation = false;

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('view_any_project') ?? false;
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $q = parent::getEloquentQuery()
            ->select([
                'projects.id',
                'projects.project_name',
                'projects.client_id',
                'projects.status',
                'projects.start_date',
                'projects.end_date',
                'projects.production_request_id',
            ])
            ->with([
                'client' => function($q) {
                    $q->select('client_id', 'client_name');
                },
                'productionRequest' => function($q) {
                    $q->select('id', 'showroom_id');
                },
                'productionRequest.showroom' => function($q) {
                    $q->select('id', 'name', 'manager_id');
                },
            ])
            ->latest('projects.id');

        $user = auth()->user();

        $isSuper = $user && method_exists($user, 'hasAnyRole')
            && $user->hasAnyRole(['admin','super-admin','owner']);

        if (! $isSuper) {
            $isShowroomManager = $user && method_exists($user, 'hasRole') && $user->hasRole('showroom_manager');
            $employeeId = $user?->employee?->getKey();

            if ($isShowroomManager) {
                if (! $employeeId) {
                    return $q->whereRaw('1 = 0');
                }

                $q->whereExists(function ($sub) use ($employeeId) {
                    $sub->from('production_requests as pr')
                        ->join('showrooms as s', 's.id', '=', 'pr.showroom_id')
                        // projects.production_request_id = pr.id
                        ->whereColumn('pr.id', 'projects.production_request_id')
                        ->where('s.manager_id', $employeeId);
                });
            }
        }

        return $q;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('production_request_id')
                ->relationship('productionRequest', 'project_name')
                ->searchable()
                ->label('طلب التصنيع')
                ->required(),

            Forms\Components\Select::make('client_id')
                ->relationship('client', 'client_name')
                ->searchable()
                ->label('العميل')
                ->required(),

            Forms\Components\TextInput::make('project_name')
                ->label('اسم المشروع')
                ->required()
                ->maxLength(255),

            Forms\Components\Textarea::make('description')
                ->label('الوصف'),

            Forms\Components\DatePicker::make('start_date')
                ->label('تاريخ البدء'),

            Forms\Components\DatePicker::make('end_date')
                ->label('تاريخ الانتهاء'),

            Forms\Components\Select::make('status')
                ->label('الحالة')
                ->options([
                    'in_progress' => 'قيد التنفيذ',
                    'completed'   => 'مكتمل',
                    'on_hold'     => 'معلّق',
                ])
                ->default('in_progress')
                ->required(),

            Forms\Components\Select::make('created_by')
                ->relationship('creator', 'name')
                ->default(fn() => Auth::id())
                ->label('أنشأه')
                ->required()
                ->hiddenOn(['edit', 'view']),

            Forms\Components\Placeholder::make('creator_name')
                ->label('أنشأه')
                ->content(fn ($record) => $record?->creator?->name ?? '—')
                ->visibleOn(['edit', 'view']),
        ]);
    }

    /**
     * @throws \Exception
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('#')->sortable(),
                TextColumn::make('project_name')->label('اسم المشروع')->searchable(),
                TextColumn::make('client.client_name')->label('العميل'),
                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'completed'   => 'success',
                        'in_progress' => 'warning',
                        'on_hold'     => 'gray',
                    }),
                TextColumn::make('start_date')->label('تاريخ البدء')->date(),
                TextColumn::make('end_date')->label('تاريخ الانتهاء')->date(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Action::make('manage-tasks')
                    ->label('توزيع المهام')
                    //->icon('heroicon-o-clipboard-list')
                    ->url(fn(Project $record) =>
                    static::getUrl('manage-tasks', ['record' => $record->getKey()])
                    ),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->filters([
                TernaryFilter::make('is_completed')
                    ->label('الحالة')
                    ->trueLabel('مكتملة فقط')
                    ->falseLabel('حالية فقط')
                    ->placeholder('الكل')
                    ->queries(
                        true:  fn (EloquentBuilder $q) => $q->where('status', 'completed'),
                        false: fn (EloquentBuilder $q) => $q->where('status', '!=', 'completed'),
                        blank: fn (EloquentBuilder $q) => $q, // لا شيء = الكل
                    )
                    ->indicateUsing(function (array $data) {
                        return match ($data['value'] ?? null) {
                            true  => 'مكتملة فقط',
                            false => 'حالية فقط',
                            default => null,
                        };
                    }),
            ])
            ->persistFiltersInSession();
    }

    public static function getRelations(): array
    {
        return [
            // TasksRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListProjects::route('/'),
            'create' => CreateProject::route('/create'),
            'view'   => ViewProject::route('/{record}'),
            'edit'   => EditProject::route('/{record}/edit'),
            'manage-tasks' => ManageProjectTasks::route('/{record}/manage-tasks'),
        ];
    }
}
