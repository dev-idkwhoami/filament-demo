<?php

namespace App\Filament\Resources\Shop\CustomerResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Squire\Models\Continent;
use Squire\Models\Country;

class AddressesRelationManager extends RelationManager
{
    protected static string $relationship = 'addresses';

    protected static ?string $recordTitleAttribute = 'full_address';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                /* Creating a select field which the country select field is dependent on to be filled in order to have any results from its query */
                Forms\Components\Select::make('continent')
                    /* Making this select field live to re-evaluate the 'disabled' condition on the country field */
                    ->live()
                    /* If a default value would be given upon the first load the select field gets initialized correctly */
                    // ->default('eu')
                    ->searchable()
                    ->getSearchResultsUsing(fn (string $query) => Continent::where('name', 'like', "%{$query}%")->pluck('name', 'id'))
                    ->getOptionLabelUsing(fn ($value): ?string => Continent::firstWhere('id', $value)?->getAttribute('name')),

                Forms\Components\Select::make('country')
                    ->searchable()
                    /* This is to prevent the user to attempt to select a country before a continent is selected. */
                    ->disabled(fn (Forms\Get $get) => ! filled($get('continent')))
                    ->getSearchResultsUsing(fn (Forms\Get $get, string $query) => Country::where('continent_id', $get('continent'))->where('name', 'like', "%{$query}%")->pluck('name', 'id'))
                    ->getOptionLabelUsing(fn (Forms\Get $get, $value): ?string => Country::where('continent_id', $get('continent'))->firstWhere('id', $value)?->getAttribute('name')),

                Forms\Components\TextInput::make('street'),

                Forms\Components\TextInput::make('zip'),

                Forms\Components\TextInput::make('city'),

                Forms\Components\TextInput::make('state'),

            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('street'),

                Tables\Columns\TextColumn::make('zip'),

                Tables\Columns\TextColumn::make('city'),

                Tables\Columns\TextColumn::make('country')
                    ->formatStateUsing(fn ($state): ?string => Country::find($state)?->name ?? null),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make(),
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DetachAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->groupedBulkActions([
                Tables\Actions\DetachBulkAction::make(),
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
