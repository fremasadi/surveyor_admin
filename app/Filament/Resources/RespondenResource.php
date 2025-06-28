<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RespondenResource\Pages;
use App\Filament\Resources\RespondenResource\RelationManagers;
use App\Models\Responden;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use App\Models\Pasar;

class RespondenResource extends Resource
{
    protected static ?string $model = Responden::class;

    protected static ?string $navigationLabel = 'Penjual'; // Sidebar label
    protected static ?string $pluralLabel = 'Penjual'; // Halaman label

    protected static ?string $navigationIcon = 'heroicon-o-archive-box-arrow-down';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                ->label('Nama')

                    ->required()
                    ->maxLength(255),
                    Select::make('pasar_id')
                    ->label('Pilih Pasar')
                    ->options(Pasar::all()->pluck('nama', 'id'))
                    ->reactive()
                    ->afterStateUpdated(fn ($state, callable $set) => $set('lokasi_pasar', Pasar::find($state)?->lokasi)),
                Forms\Components\TextInput::make('contact')
                ->label('No.Telepon')

                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable(),
                Tables\Columns\TextColumn::make('address')
                    ->label('Alamat')
                    ->searchable(),
                Tables\Columns\TextColumn::make('contact')
                    ->label('No.Telepon')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRespondens::route('/'),
            'create' => Pages\CreateResponden::route('/create'),
            'edit' => Pages\EditResponden::route('/{record}/edit'),
        ];
    }
}
