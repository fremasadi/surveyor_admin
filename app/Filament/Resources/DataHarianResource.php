<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DataHarianResource\Pages;
use App\Filament\Resources\DataHarianResource\RelationManagers;
use App\Models\DataHarian;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\SelectFilter;
use App\Models\Pasar;

class DataHarianResource extends Resource
{
    protected static ?string $model = DataHarian::class;

    protected static ?string $navigationLabel = 'Data Harian'; // Sidebar label
    protected static ?string $pluralLabel = 'Data Harian'; // Halaman label
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('user_id')
                    ->label('Surveyor')
                    ->relationship(
                        name: 'user',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn (Builder $query) => $query->where('role', 'surveyor')
                    )
                    ->required(),

                Forms\Components\Select::make('komoditas_id')
                    ->label('Komoditas')
                    ->relationship('komoditas', 'name') // Menggunakan relasi untuk mengambil nama komoditas
                    ->required(),

                Forms\Components\Select::make('responden_id')
                    ->label('Penjual')
                    ->relationship('responden', 'name') // Menggunakan relasi untuk mengambil nama responden
                    ->required(),
                

                Forms\Components\DatePicker::make('tanggal')
                    ->label('Tanggal')
                    ->required(),

                Forms\Components\Toggle::make('status')
                    ->label('Status (Acc/Not)')
                    ->required(),

                TextInput::make('data_input')
                    ->label('Harga')
                    ->numeric() // Hanya menerima angka
                    ->required()
                    ->minValue(0), // Opsional: Mencegah nilai negatif
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Nama Surveyor')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('komoditas.name')
                    ->label('Nama Komoditas')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('responden.name')
                    ->label('Nama Penjual')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('responden.address')
                    ->label('Alamat Penjual')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('data_input')
                    ->label('Harga Input')
                    ->searchable(),
                    Tables\Columns\TextColumn::make('komoditas.satuan')
                    ->label('Satuan')
                    ->sortable()
                    ->searchable(),
                    Tables\Columns\ToggleColumn::make('status')
                    ->label('Status')
                    ->onIcon('heroicon-o-check') // Ikon untuk status true
                    ->offIcon('heroicon-o-x-circle') // Ikon untuk status false
                    ->onColor('success') // Warna hijau untuk status true
                    ->offColor('danger') // Warna merah untuk status false
                    ->sortable()
                    ->searchable(),

                // Tables\Columns\TextColumn::make('created_at')
                //     ->label('Dibuat')
                //     ->dateTime()
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                // Filter: Range Tanggal
                Filter::make('tanggal')
                ->form([
                    DatePicker::make('from')->label('Dari Tanggal'),
                    DatePicker::make('until')->label('Sampai Tanggal'),
                ])
                ->query(function ($query, array $data) {
                    return $query
                        ->when($data['from'], fn ($q) => $q->whereDate('tanggal', '>=', $data['from']))
                        ->when($data['until'], fn ($q) => $q->whereDate('tanggal', '<=', $data['until']));
                }),
                // Filter: Komoditas
                SelectFilter::make('komoditas_id')
                ->label('Nama Komoditas')
                ->relationship('komoditas', 'name'),

                // Filter: Responden
                SelectFilter::make('responden_id')
                    ->label('Nama Penjual')
                    ->relationship('responden', 'name'),

                // Filter: Status
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        true => 'Aktif',
                        false => 'Tidak Aktif',
                    ]),
                    
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                
            ])            
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListDataHarians::route('/'),
            'create' => Pages\CreateDataHarian::route('/create'),
            'edit' => Pages\EditDataHarian::route('/{record}/edit'),
        ];
    }
}
