<?php

namespace SBC\CodeMakerBundle\service;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\EntityManager;
use Psr\Container\ContainerInterface;
use SBC\CodeMakerBundle\Annotation\CodeMaker;
use SBC\CodeMakerBundle\Annotation\DiscriminationColumn;
use SBC\CodeMakerBundle\DependencyInjection\Configuration;
use SBC\CodeMakerBundle\Entity\Generator;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * @package SBC\CodeMakerBundle\service
 * @author: Omar Ismail <omarismail.omar@gmail.com>
 **/
class CodeMakerService
{
    /**
     * @var ContainerInterface
     */
    private $container;
    /**
     * @var EntityManager
     */
    private $em;
    /**
     * @var Configuration
     */
    private $config;

    function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        //$this->em = $container->get('doctrine.orm.entity_manager');
    }

    public function setConfig($config)
    {
        $this->config = $config;
    }

    /**
     * Validate Generator object or render exception(s)
     *
     * @param Generator $generator
     */
    public function validate_generator(Generator $generator)
    {
        //check_generator_conflict through the displayName mentioned in CodeMaker
        $display_name = $generator->getDisplayName();
        $this->check_generator_conflict($display_name);

        // check_annotation_conflict through the DisplayName mentioned in CodeMaker
        $class_name = $this->check_annotation_conflict($generator->getDisplayName());
        $generator->setClassName($class_name);

        // check the simplePattern
        $this->check_simple_pattern($generator->getSimplePattern());

        // generate pregX pattern from simple pattern
        $preg_pattern = $this->generate_preg_pattern($generator->getSimplePattern());
        $generator->setPregPattern($preg_pattern);

        // check lastCode
        $this->check_last_code($generator);

    }

    /**
     * Generate new PregX pattern from validate simple pattern
     *
     * @param $simple_pattern
     * @return string : the generated pregX pattern
     */
    public function generate_preg_pattern($simple_pattern)
    {
        $preg_pattern = $simple_pattern;
        /**
         * PATTERN OPTIONAL characters (A|B|C)
         */
//        if (preg_match_all("#\(___([a-zA-Z0-9]+)___\)#", $preg_pattern, $matches)) {
//            foreach ($matches[1] as $optionsFragment) {
//                $optionsValues = chunk_split($optionsFragment, 1, '|');
//                $optionsValues = substr($optionsValues, 0, strlen($optionsValues) - 1);
//                $preg_pattern = preg_replace("#\(___" . $optionsFragment . "___\)#", "([" . $optionsValues . "])", $preg_pattern);
//            }
//        }

        /**
         * PATTERN DYNAMIC years (__yy__)
         */
        if (preg_match("#\(__[y]{2}__\)#", $preg_pattern)) {
            $preg_pattern = preg_replace("#\(__[y]{2}__\)#", "([0-4]\d|50)", $preg_pattern);
        }

        /**
         * PATTERN DYNAMIC months (__mm__)
         */
        if (preg_match("#\(__[m]{2}__\)#", $preg_pattern)) {
            $preg_pattern = preg_replace("#\(__[m]{2}__\)#", "(0[1-9]|10|11|12)", $preg_pattern);
        }

        /**
         * PATTERN DYNAMIC counters (__iii__)
         */
        if (preg_match_all("#\(__([i]+[f,y,m]?)__\)#", $preg_pattern, $matches)) {
            foreach ($matches[1] as $compteurFragment) {
                $compteurLength = strlen($compteurFragment) - 1;
                $preg_pattern = preg_replace(
                    "#\(__[i]{" . $compteurLength . "}[f,y,m]?__\)#",
                    "([0-9]{" . $compteurLength . "})",
                    $preg_pattern);
            }
        }

        /**
         * PATTERN any alphanumeric text (__*__)
         */
        if (preg_match_all("#\(__any__\)#", $preg_pattern, $matches)) {
            foreach ($matches[0] as $anyString) {
                $preg_pattern = preg_replace("#\(__any__\)#", "([\w-]+)", $preg_pattern);
            }
        }
        return '#^' . $preg_pattern . '$#';
    }

    /**
     * Generate new code from the available entity generator
     *
     * @param $object : the entity that we want to make a code maker
     * @return string : the new generated code for this object(entity)
     */
    public function generate_new_code($object, $discriminatorValue = null, $random = false)
    {
        $new_code = "";
        $annotation = $this->get_annotation($object);
        $generator = $annotation instanceof CodeMaker ? $this->get_generator($annotation, $discriminatorValue) : null;
        if ($annotation instanceof CodeMaker && $generator instanceof Generator) {

            $currentYear = Date('y');
            $currentMonth = Date('m');
            $year_changed = ($generator->getUpdatedAt()->format('y') < $currentYear) ? true : false;
            $month_changed = ($year_changed || $generator->getUpdatedAt()->format('m') < $currentMonth) ? true : false;

//            var_dump($year_changed);
//            var_dump($month_changed);
//            die();
            $newCode_array = array();
            $counters_array = array();

            $this->check_simple_pattern($generator->getSimplePattern());
            $this->check_annotation_conflict($generator->getDisplayName());
            $valid_matches = $this->check_last_code($generator);

            if (!empty($valid_matches)) {
                $lastCode_array = $valid_matches;
                /**
                 * Simple pattern fragmentation
                 */
                if (preg_match_all("#\(([^()]|(?R))+\)#", $generator->getSimplePattern(), $matches)) {
                    foreach ($matches[0] as $i => $item) {
                        $i++;
                        if ($item === '(/)' || $item === '(\)' || $item === '(-)' || $item === '(_)') {
                            $newCode_array[$i] = $lastCode_array[$i];
                        } else if ($item === '(__any__)') {
                            $newCode_array['(__any__)'] = $lastCode_array[$i];
                        } else {
                            $newCode_array[$item] = $lastCode_array[$i];
                        }
                    }
                }

                /**
                 * Prepare the newCode
                 */
                foreach ($newCode_array as $key => $item) {

                    if (preg_match("#(__yy__)#", $key, $matches)) {
                        $newCode_array[$key] = $currentYear;
                        unset($matches);
                    }
                    if (preg_match("#(__mm__)#", $key, $matches)) {
                        $newCode_array[$key] = $currentMonth;
                        unset($matches);
                    }
                    if (preg_match("#__([i]+[f,y,m]?)__#", $key, $matches)) {
                        $counterFragment = $matches[1];
                        $counterLength = strlen($counterFragment);
                        $counter = array();
                        $counter['key'] = $key;
                        $counter['length'] = $counterLength - 1;
                        $counter['increment'] = substr($counterFragment, -1);
                        $counters_array[] = $counter;
                    }

                    if (preg_match("#(__any__)#", $key, $matches)) {
                        foreach ($matches as $anyString) {
                            $newCode_array[$key] = "__any__";
                        }
                        unset($matches);
                    }
                }

                /**
                 * Update numeric counters
                 */
                foreach ($counters_array as $counter) {
                    $key = $counter['key'];
                    $length = $counter['length'];
                    $increment = $counter['increment'];

                    if( ($year_changed && $increment === "y") ){
                        $newCode_array[$key] = str_pad("1", $length, '0', STR_PAD_LEFT);
                    } elseif ( ($month_changed && $increment === "m") ){
                        $newCode_array[$key] = str_pad("1", $length, '0', STR_PAD_LEFT);
                    } else{
                        $newCode_array[$key] = str_pad(($newCode_array[$key] + 1), $length, '0', STR_PAD_LEFT);
                    }
                }
                $new_code = implode($newCode_array);
            }
        }

        if ($random) $new_code = $this->generate_random_code();
        return $new_code;
    }

    /**
     * Update the last code in generator of this object(entity)
     *
     * @param $object : the entity we want to update their generator code
     * @return void : update the generator code of this object(entity)
     */
    public function update_last_code($object)
    {
        $accessor = PropertyAccess::createPropertyAccessor();
        $annotation = $this->get_annotation($object);
        $discriminatorValue = null;
        if ($annotation->discriminatorColumn !== null) {
            $discriminatorValue = $accessor->getValue($object, $annotation->discriminatorColumn);
        }
        $generator = $this->get_generator($annotation, $discriminatorValue);

        if ($generator instanceof Generator) {
            $last_code = $accessor->getValue($object, $annotation->getCodeColumn());
            $generator->setLastCode($last_code);
            $generator->setUpdatedAt(new \DateTime('now'));
            $matches = $this->check_last_code($generator);

            if (empty ($matches)) {
                // does not update the generator if lastCode not matched within pregX pattern
                $this->em->refresh($generator);
            } else {
                // update the generator if lastCode matched within pregX pattern
                $this->em->persist($generator);
            }
        }
    }

    /**
     * Check if the syntax of the simple pattern is correct
     *
     * @param $simple_pattern
     * @return bool
     * @throws \Exception if simple_pattern not valid
     */
    public function check_simple_pattern($simple_pattern)
    {
        $isCorrect = true;
        /**
         *
         * #^\([a-zA-Z0-9]+\)$#
         * #^\([\s\S]+\)$#
         * ^\([_-a-zA-Z0-9\\\/]+\)$
         * #^(\()([a-zA-Z0-9]+)(\))$#"
         */
        if (!preg_match_all('~^(?:\([\w\\\/-]+\))+$~', $simple_pattern, $matches, PREG_OFFSET_CAPTURE)) {
            throw new \Exception("SBC\CodeMakerBundle\service\CodeMaker
            [check_simple_pattern(" . $simple_pattern . ")] : 
            The simple pattern " . $simple_pattern . " is not valid !");
        }
        return $isCorrect;
    }

    /**
     * Check if the syntax of the last code is correct
     * within the pregX pattern of the generator
     *
     * @param Generator $generator
     * @return array|null : the available matches between (last_code and pregX)
     * @throws \Exception
     */
    public function check_last_code(Generator $generator, $force_check = false)
    {
        //$global_respect_pattern = $this->config['respect_pattern'];
        $self_respect_pattern = $generator->isRespectPattern();
        $valid_matches = null;
        if ($force_check) $self_respect_pattern = true;
        if (!preg_match($generator->getPregPattern(), $generator->getLastCode(), $matches) && $self_respect_pattern) {
            var_dump($matches);
            throw new \Exception("SBC\CodeMakerBundle\service\CodeMaker
            [check_last_code(" . $generator->getClassName() . ")] : Pattern Regx incompatible with LastCode
            RegXPattern = " . $generator->getPregPattern() . "
            SimplePattern = " . $generator->getSimplePattern() . "
            LastCode = " . $generator->getLastCode());
        } else {
            $valid_matches = $matches;
        }
        return $valid_matches;
    }

    /**
     * Check if there is a conflict in @CodeMaker annotation with this display_name
     *
     * @param $display_name
     * @return string|null : the name space of the entity if any conflict detected
     * @throws \Exception
     */
    public function check_annotation_conflict($display_name)
    {
        $counter = 0;
        $class_name = null;
        $entities_with_maker = $this->get_entities_with_maker(true);

        foreach ($entities_with_maker as $item) {
            $className = $item['className'];
            $annotation = $item['makerAnnotation'];

            if ($annotation->discriminatorColumn === null) {
                $displayName = $annotation->displayName;
                if ($displayName === $display_name) {
                    $counter++;
                    $class_name = $className;
                }
            } else { // case that @CodeMaker annotation with discriminator columns
                $discriminations = $annotation->discriminations;
                foreach ($discriminations as $discriminationColumn) {
                    $displayName = $discriminationColumn->displayName;
                    if ($displayName === $display_name) {
                        $counter++;
                        $class_name = $className;
                    }
                }
            }
        }

        if ($counter === 0) {
            throw new \Exception("SBC\CodeMakerBundle\service\CodeMaker
            [ check_annotation_conflict (" . $display_name . ") ]
            @CodeMaker does not implemented in any Class with displayName = \"" . $display_name . "\" !");

        } elseif ($counter > 1) {
            throw new \Exception("SBC\CodeMakerBundle\service\CodeMaker
            [ check_annotation_conflict (" . $display_name . ") ]
            @CodeMaker redundancy conflict has been detected in class 
            " . $class_name . " with displayName = \"" . $display_name . "\" !");
        }
        return $class_name;
    }

    /**
     * Check if there is a conflict in Generator with this display_name
     *
     * @param $display_name
     * @throws \Exception : if generator already created for entity with this display_name
     */
    public function check_generator_conflict($display_name)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $generator = $em->getRepository('CodeMakerBundle:Generator')->findOneBy(array(
            'displayName' => $display_name
        ));
        if ($generator instanceof Generator) {
            throw new \Exception("SBC\CodeMakerBundle\service\CodeMaker
            [ check_generator_conflict (" . $display_name . ") ]
            Generator already created for entity \"" . $generator->getClassName() . "\"
            with displayName = \"" . $display_name . "\" !");
        }
    }

    /**
     * Returns a list with or without redundancy for the entity that implements the CodeMaker annotation
     * @param bool $withRedundancy
     * @return array
     */
    public function get_entities_with_maker($withRedundancy = false)
    {
        $this->em = $this->container->get('doctrine.orm.entity_manager');
        $class_names = $this->em->getConfiguration()->getMetadataDriverImpl()->getAllClassNames();
        $entities_with_maker = array();
        foreach ($class_names as $class_name) {
            $annotation = $this->get_annotation($class_name);
            if ($annotation instanceof CodeMaker) {

                $entities_with_maker[] = array(
                    'className' => $class_name,
                    'makerAnnotation' => $annotation
                );
            }
        }
        return $entities_with_maker;
    }

    /**
     * Returns the generator object of entity through the object
     * of entity or the $display_name value that mentioned in CodeMaker annotation
     *
     * @param $object
     * @param null $display_name
     * @return null|Generator
     */
    public function get_generator(CodeMaker $makerAnnotation, $discriminatorValue = null)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $generator = null;

        if ($discriminatorValue === null) {
            $generator = $em->getRepository('CodeMakerBundle:Generator')->findOneBy(array(
                'displayName' => $makerAnnotation->displayName
            ));
        } else {
            $discriminations = $makerAnnotation->discriminations;
            foreach ($discriminations as $discriminationColumnAnnotation) {

                if ($discriminationColumnAnnotation->value === $discriminatorValue) {
                    $generator = $em->getRepository('CodeMakerBundle:Generator')->findOneBy(array(
                        'displayName' => $discriminationColumnAnnotation->displayName
                    ));
                    break 1;
                }
            }
        }
        return $generator;
    }

    /**
     * Returns the object of @CodeMaker through the $object of entity class
     *
     * @param $object
     * @param string $annotation
     * @return null|@CodeMaker object
     */
    public function get_annotation($object, $annotation = CodeMaker::class)
    {
        // Get \ReflectionClass for object
        $reflectionClass = new \ReflectionClass($object);
        // Prepare doctrine annotation reader
        $reader = new AnnotationReader();
        $annotation = $reader->getClassAnnotation($reflectionClass, $annotation);
        return $annotation;
    }

    public function get_discrimination_by_value(Array $discriminations, $discriminatorValue)
    {
        /**
         * @var DiscriminationColumn
         */
        $discriminationColumnAnnotation = null;
        foreach ($discriminations as $discrimination) {
            if ($discrimination->value === $discriminatorValue) {
                $discriminationColumnAnnotation = $discrimination;
                break 1;
            }
        }
        return $discriminationColumnAnnotation;
    }

    /**
     * @return int
     */
    public function generate_random_code()
    {
        return abs(intval(hexdec(uniqid())));
    }

}