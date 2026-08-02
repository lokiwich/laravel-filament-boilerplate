<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name')
                    ->label(__('users.fields.name')),
                TextEntry::make('email')
                    ->label(__('users.fields.email')),
                TextEntry::make('email_verified_at')
                    ->label(__('users.fields.verified'))
                    ->dateTime()
                    ->placeholder(__('users.placeholders.unverified')),
                TextEntry::make('keycloak_id')
                    ->label(__('users.fields.keycloak_id'))
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->label(__('users.fields.created_at'))
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->label(__('users.fields.updated_at'))
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
