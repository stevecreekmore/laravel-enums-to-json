<?php

namespace Creekmore108\LaravelEnumsToJson\Commands;

use BackedEnum;
use Illuminate\Console\Command;
use Spatie\StructureDiscoverer\Discover;
use Creekmore108\LaravelEnumsToJson\Attributes\EnumToJson;
use Illuminate\Support\Collection;
use Creekmore108\LaravelEnumsToJson\Exceptions\LaravelEnumToJsonException;
use Illuminate\Support\Facades\Storage;

class LaravelEnumsToJsonCommand extends Command
{
    public $signature = 'enum-to-json:generate';

    public $description = 'It creates json file from attributed enums ';

    public function handle(): int
    {
        $output = [];

        $this->getQualifiedEnums()
            ->each(function (BackedEnum|string $enum) use (&$output) {
                $fileName = $this->generateFileName($enum);

                if ($output[$fileName] ?? false) {
                    throw LaravelEnumToJsonException::nameCollision($fileName);
                }

                $output[$fileName] = $this->prepareEnumData($enum);
            });

        foreach ($output as $fileName => $contents) {
            Storage::disk(config('enums-to-json.disk'))
                ->put(
                    str(config('enums-to-json.path') . '/' . $fileName)->replace('//', '/')->toString(),
                    json_encode($contents),
                );
        }

        $this->info("Generated " . count($output) . ' files');

        return self::SUCCESS;
    }

    protected function getQualifiedEnums():collection
    {
        $enums = Discover::in(...config('enums-to-json.enum_locations'))
            ->enums()
            ->withAttribute(EnumToJson::class)
            ->get();

        return collect($enums);
    }

    protected function prepareEnumData(BackedEnum|string $enum): Collection
    {
        return collect($enum::cases())
            ->map(function ($el) {
                if (method_exists($el, 'toJson')) {
                    return $el->toJson();
                }

                return [
                    'label' => config('enums-to-json.format_label')
                        ? implode(' ', array_filter(preg_split('/(?=[A-Z])/', $el->name)))
                        : $el->name,
                    'value' => $el->value,
                ];
            });
    }

    protected function generateFileName(BackedEnum|string $enum): string
    {
        if (method_exists($enum, 'jsonFileName')) {
            return str($enum::jsonFileName())->finish('.json')->toString();
        }

        return str($enum)->classBasename()->snake()->append('.json')->toString();
    }
}
