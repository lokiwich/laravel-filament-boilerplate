<?php

namespace App\Filament\Resources\Admins\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class AdminInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name')
                    ->label(__('admins.fields.name')),
                TextEntry::make('email')
                    ->label(__('admins.fields.email')),
                TextEntry::make('roles.name')
                    ->label(__('admins.fields.roles'))
                    ->badge()
                    ->placeholder('-'),
                TextEntry::make('email_verified_at')
                    ->label(__('admins.fields.verified'))
                    ->dateTime()
                    ->placeholder(__('admins.placeholders.unverified')),
                TextEntry::make('keycloak_id')
                    ->label(__('admins.fields.keycloak_id'))
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->label(__('admins.fields.created_at'))
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->label(__('admins.fields.updated_at'))
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
