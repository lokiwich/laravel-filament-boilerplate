<?php

namespace App\Filament\Resources\ActivityLogs\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ActivityLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('description')
                    ->label(__('activity_logs.fields.description'))
                    ->searchable()
                    ->wrap(),
                TextColumn::make('event')
                    ->label(__('activity_logs.fields.event'))
                    ->badge()
                    ->placeholder('-'),
                TextColumn::make('causer.name')
                    ->label(__('activity_logs.fields.caused_by'))
                    ->placeholder(__('activity_logs.placeholders.system'))
                    ->searchable(),
                TextColumn::make('subject_type')
                    ->label(__('activity_logs.fields.subject'))
                    ->formatStateUsing(fn (?string $state, $record): string => filled($state)
                        ? class_basename($state).($record->subject_id ? " #{$record->subject_id}" : '')
                        : '-')
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label(__('activity_logs.fields.when'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('event')
                    ->label(__('activity_logs.fields.event'))
                    ->options(fn (): array => \Spatie\Activitylog\Models\Activity::query()
                        ->whereNotNull('event')
                        ->distinct()
                        ->orderBy('event')
                        ->pluck('event', 'event')
                        ->all()),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([]);
    }
}
