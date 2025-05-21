<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KomoditasResource\Pages;
use App\Filament\Resources\KomoditasResource\RelationManagers;
use App\Models\Komoditas;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Select;


class KomoditasResource extends Resource
{
    protected static ?string $model = Komoditas::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nama Komoditas')
                    ->required()
                    ->maxLength(255),
                Forms\Components\FileUpload::make('image')
                    ->label('Gambar Komoditas')
                    ->image(),
                    Select::make('satuan')
                    ->label('Satuan')
                    ->options([
                        'kg' => 'Kilogram (kg)',
                        'g' => 'Gram (g)',
                        'liter' => 'Liter (L)',
                        'ml' => 'Mililiter (ml)',
                        'pcs' => 'Pieces (pcs)',
                        'pack' => 'Pack',
                    ])
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Komoditas')
                    ->searchable(),
                Tables\Columns\ImageColumn::make('image')
                ->label('Gambar Komoditas'),
                Tables\Columns\TextColumn::make('satuan')
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
            'index' => Pages\ListKomoditas::route('/'),
            'create' => Pages\CreateKomoditas::route('/create'),
            'edit' => Pages\EditKomoditas::route('/{record}/edit'),
        ];
    }
}
