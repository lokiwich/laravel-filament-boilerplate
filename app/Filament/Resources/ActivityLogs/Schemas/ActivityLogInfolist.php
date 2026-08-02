<?php

namespace App\Filament\Resources\ActivityLogs\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ActivityLogInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('description')
                    ->label(__('activity_logs.fields.description')),
                TextEntry::make('event')
                    ->label(__('activity_logs.fields.event'))
                    ->badge()
                    ->placeholder('-'),
                TextEntry::make('causer.name')
                    ->label(__('activity_logs.fields.caused_by'))
                    ->placeholder(__('activity_logs.placeholders.system')),
                TextEntry::make('subject_type')
                    ->label(__('activity_logs.fields.subject_type'))
                    ->formatStateUsing(fn (?string $state): string => filled($state) ? class_basename($state) : '-')
                    ->placeholder('-'),
                TextEntry::make('subject_id')
                    ->label(__('activity_logs.fields.subject_id'))
                    ->placeholder('-'),
                TextEntry::make('properties')
                    ->label(__('activity_logs.fields.properties'))
                    ->formatStateUsing(fn ($state): string => filled($state)
                        ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                        : '-')
                    ->columnSpanFull(),
                TextEntry::make('created_at')
                    ->label(__('activity_logs.fields.created_at'))
                    ->dateTime(),
            ]);
    }
}
