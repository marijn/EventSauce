<?php

namespace EventSauce\EventSourcing\CodeGeneration;

use function file_get_contents;
use PHPUnit\Framework\TestCase;

class CodeDumperTest extends TestCase
{
    /**
     * @test
     */
    public function dumping_a_definition()
    {
        $groups = $this->definitionProvider();

        foreach ($groups as $group) {
            /** @var DefinitionGroup $definitionGroup */
            /** @var string $fixtureFile */
            list ($definitionGroup, $fixtureFile) = $group;
            $dumper = new CodeDumper();
            $actual = $dumper->dump($definitionGroup);
            // file_put_contents(__DIR__ . '/Fixtures/' . $fixtureFile . 'Fixture.php', $actual);
            $expected = file_get_contents(__DIR__ . '/Fixtures/' . $fixtureFile . 'Fixture.php');
            $this->assertEquals($expected, $actual);
        }
    }

    public function definitionProvider()
    {
        /* test case 1 */
        $simpleDefinitionGroup = DefinitionGroup::create('Simple\\Definition\\Group');
        $simpleDefinitionGroup->event('SomethingHappened')
            ->field('what', 'string', 'Example Event')
            ->field('yolo', 'bool', 'true');

        /* test case 2 */
        $multipleEventsDefinitionGroup = DefinitionGroup::create('Multiple\\Events\\DefinitionGroup');
        $multipleEventsDefinitionGroup->event('FirstEvent')
            ->field('firstField', 'string', 'FIRST');
        $multipleEventsDefinitionGroup->event('SecondEvent')
            ->field('secondField', 'string', 'SECOND');

        /* test case 3 */
        $definitionGroupWithDefaults = DefinitionGroup::create('Group\\With\\Defaults');
        $definitionGroupWithDefaults->fieldDefault('description', 'string', 'This is a description.');
        $definitionGroupWithDefaults->event('EventWithDescription')
            ->field('description');

        /* test case 4 */
        $groupWithFieldSerialization = DefinitionGroup::create('Group\\With\\FieldDeserialization');
        $groupWithFieldSerialization->fieldSerializer('items', <<<EOF
array_map(function (\$item) {
    return \$item['property'];
}, {param})
EOF
);
        $groupWithFieldSerialization->fieldDeserializer('items', <<<EOF
array_map(function (\$property) {
    return ['property' => \$property];
}, {param})
EOF
);
        $groupWithFieldSerialization->event('WithFieldSerializers')
            ->field('items', 'array');

        /* test case 5 */
        $groupWithVersionEvent = DefinitionGroup::create('With\Versioned\Event');
        $groupWithVersionEvent->event('VersionTwo')
            ->atVersion(2);

        $definitionGroupWithCommand = DefinitionGroup::create('With\Commands');
        $definitionGroupWithCommand->command('DoSomething')
            ->field('reason', 'string', 'Because reasons.');

        /* test case 6 */
        $groupWithFieldSerializationFromEvent = DefinitionGroup::create('With\\EventFieldSerialization');
        $groupWithFieldSerializationFromEvent->event('EventName')
            ->field('title', 'string', 'Title')
            ->fieldSerializer('title', <<<EOF
strtoupper({param})
EOF
)->fieldDeserializer('title', <<<EOF
strtolower({param})
EOF
);

        return [
            [$simpleDefinitionGroup, 'simpleDefinitionGroup'],
            [$multipleEventsDefinitionGroup, 'multipleEventsDefinitionGroup'],
            [$definitionGroupWithDefaults, 'definitionGroupWithDefaults'],
            [$groupWithFieldSerialization, 'groupWithFieldSerialization'],
            [$groupWithVersionEvent, 'groupWithVersionEvent'],
            [$definitionGroupWithCommand, 'definitionGroupWithCommand'],
            [$groupWithFieldSerializationFromEvent, 'groupWithFieldSerializationFromEvent'],
        ];
    }
}