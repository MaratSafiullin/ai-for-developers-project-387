<?php

/**
 * Hand-written Russian validation messages. Only the rules and attributes
 * the API actually uses are listed; add a line here with every new rule.
 */
return [

    'date' => 'Поле «:attribute» должно быть существующей датой.',
    'exists' => 'Выбранное значение поля «:attribute» не найдено.',
    'integer' => 'Поле «:attribute» должно быть целым числом.',
    'max' => [
        'numeric' => 'Поле «:attribute» должно быть не больше :max.',
        'string' => 'Поле «:attribute» должно быть не длиннее :max символов.',
    ],
    'min' => [
        'numeric' => 'Поле «:attribute» должно быть не меньше :min.',
    ],
    'multiple_of' => 'Поле «:attribute» должно быть кратно :value.',
    'regex' => 'Поле «:attribute» имеет неверный формат.',
    'required' => 'Заполните поле «:attribute».',
    'string' => 'Поле «:attribute» должно быть строкой.',

    'attributes' => [
        'name' => 'Название',
        'description' => 'Описание',
        'durationMinutes' => 'Длительность',
        'meetingTypeId' => 'Тип встречи',
        'guestName' => 'Имя',
        'startsAt' => 'Время',
    ],

];
