<?php

namespace App\Filament\Resources\General;

use App\Filament\Resources\General\EstablecimientoResource\Pages;
use App\Models\General\Establecimiento;
use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Actions\Action;
use Filament\Tables\Columns\TextColumn;

class EstablecimientoResource extends Resource
{
    protected static ?string $model = Establecimiento::class;

    protected static ?string $navigationIcon = 'heroicon-s-building-library';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('empresa_id')
                    ->disabledOn('edit')
                    ->label(__('labels.establecimientos.empresa'))
                    ->required()
                    ->searchable()
                    ->relationship('empresa', 'nombre_comercial'),
                Forms\Components\TextInput::make('descripcion')
                    ->label(__('labels.establecimientos.descripcion'))
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('codigo_institucion')
                    ->disabledOn('edit')
                    ->label(__('labels.establecimientos.codigo_institucion'))
                    ->required()
                    ->maxLength(3),
                Forms\Components\TextInput::make('direccion')
                    ->label(__('labels.establecimientos.direccion'))
                    ->required()
                    ->maxLength(300),
                Repeater::make('puntosEmision')
                    ->columnSpan(2)
                    ->relationship()
                    ->schema([
                        Select::make('tipo_comprobante_id')
                            ->relationship('tipoComprobante', 'nombre')
                            ->disabledOn('edit')
                            ->columnSpan(2)
                            ->label(__('labels.establecimientos.tipo_comprobante'))
                            ->required(),
                        TextInput::make('nombre')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('codigo_emisor')
                            ->disabledOn('edit')
                            ->required()
                            ->integer()
                            ->maxLength(3),
                        TextInput::make('numero_inicial')
                            ->required()
                            ->integer()
                            ->maxLength(9),
                        Toggle::make('electronico')
                            ->disabledOn('edit')
                            ->inline()
                            ->label(__('labels.establecimientos.electronico'))
                            ->default(true)
                    ])
                    ->addActionLabel(__('actions.establecimientos.add_punto_emision'))
                    ->label(__('labels.establecimientos.puntos_emision'))
                    ->deleteAction(
                        fn (Action $action) => $action->requiresConfirmation(),
                    )
                    ->columns(3)
                    ->cloneable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('empresa.nombre_comercial')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('descripcion')
                    ->label(__('columns.establecimientos.descripcion'))
                    ->searchable(),
                TextColumn::make('codigo_institucion')
                    ->label(__('columns.establecimientos.codigo_institucion'))
                    ->searchable(),
                TextColumn::make('direccion')
                    ->label(__('columns.establecimientos.direccion'))
                    ->searchable(),
                TextColumn::make('puntos_emision_count')
                    ->label(__('columns.establecimientos.puntos_emision'))
                    ->counts('puntosEmision'),
                TextColumn::make('created_at')
                    ->label(__('columns.general.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('columns.general.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->label(__('columns.general.deleted_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('empresa')
                    ->searchable()
                    ->multiple()
                    ->relationship('empresa', 'nombre_comercial')
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEstablecimientos::route('/'),
            'create' => Pages\CreateEstablecimiento::route('/create'),
            'edit' => Pages\EditEstablecimiento::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
