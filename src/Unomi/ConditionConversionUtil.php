<?php
/**
 *
 *
 */

namespace Bolius\UnomiClient\Unomi;


use Symfony\Component\Yaml\Yaml;

class ConditionConversionUtil
{

    public static function convertYamlToUnomi($yaml)
    {
        $in = Yaml::parse($yaml);
        $b = null;
        if (is_array($in)) {

            $b = [
                'type' => 'booleanCondition',
                'parameterValues' => [
                    'operator' => 'and',
                ],

            ];

            foreach ($in as $property => $value) {
                $b['parameterValues']['subConditions'][] = [

                    'type' => 'profilePropertyCondition',
                    'parameterValues' => [
                        'propertyName' => 'properties.' . $property,
                        'comparisonOperator' => 'equals',
                        'propertyValue' => $value,
                    ],
                ];
            }
        }

        return $b;
    }

    /*
     * Convert https://querybuilder.js.org/
     * to Unomi syntax
     *
     * @param
     * @
     */
    public static function convertQueryBuilderToUnomi($rule)
    {

        $parts = explode(':', $rule['id']);

        if (isset($rule['rules'])) {

            $b = [
                'type' => 'booleanCondition',
                'parameterValues' => [
                    'operator' => $rule['operator'],
                ],

            ];

            foreach ($rule['rules'] as $subRule) {
                $b['parameterValues']['subConditions'][] = self::convertQueryBuilderToUnomi($subRule);
            }
        } else {

            switch ($rule['operator']) {
                case 'equal':
                    $operator = 'equals';
                    break;
            }

            switch ($parts[0]) {

                case 'profile' :

                    switch ($parts[1]) {
                        case 'segment' :
                            $matchType = 'in';
                            switch ($rule['operator']) {
                                case 'in':
                                    $matchType = 'in';
                                    break;
                                case 'not_in' :
                                    $matchType = 'notIn';
                                    break;
                            }
                            $b = [
                                'type' => 'profileSegmentCondition',
                                'parameterValues' => [
                                    'matchType' => $matchType,
                                    'segments' => $rule['value'],
                                ],
                            ];
                            break;
                    }
                    break;


                case 'profileProperty' :
                    $b = [
                        'type' => 'profilePropertyCondition',
                        'parameterValues' => [
                            'propertyName' => $parts[1],
                            'comparisonOperator' => $operator,
                            'propertyValue' => $rule['value'],
                        ],
                    ];
                    break;

            }
        }

        return $b;

    }

    /**
     * @param array $unomiCondition
     * @return array
     */
    public static function convertUnomiToQueryBuilder($unomiCondition)
    {

        /*
         *
         {"condition":"AND","rules":[{"condition":"AND","rules":[{"id":"profile:segment","field":"profile:segment","type":"string","input":"select","operator":"equal","value":["at_least_5_pageviews"]}]}],"valid":true}

         */
        $rules = [];

        switch ($unomiCondition['parameterValues']['comparisonOperator']) {
            case 'equals':
                $operator = 'equal';
                break;
        }

        switch ($unomiCondition['type']) {
            case 'booleanCondition' :

                $rule = [
                    'operator' => $unomiCondition['parameterValues']['operator'],
                ];
                foreach ($unomiCondition['parameterValues']['subConditions'] as $subCondition) {
                    $rule['rules'][] = self::convertUnomiToQueryBuilder($subCondition);
                }
                $rules[] = $rule;
                break;

            case 'profilePropertyCondition' :
                $rule = [
                    'id' => 'profileProperty:' . $unomiCondition['parameterValues']['propertyName'],
                    'field' => 'profileProperty:' . $unomiCondition['parameterValues']['propertyName'],
                    'type' => 'string',
                    'input' => 'text',
                    'operator' => $operator,
                    'value' => $unomiCondition['parameterValues']['propertyValue'],
                ];
                break;

            /**
             * QueryBuilderJs can't handle this
             */
            case 'pastEventCondition' :
                return FALSE;
                break;
        }

        return $rule;
    }
}