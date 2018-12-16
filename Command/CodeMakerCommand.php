<?php

namespace SBC\CodeMakerBundle\Command;

use Doctrine\ORM\EntityManager;
use SBC\CodeMakerBundle\Entity\Generator;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

/**
 * @package SBC\CodeMakerBundle\Annotation
 * @author: Omar Ismail <omarismail.omar@gmail.com>
 **/
class CodeMakerCommand extends ContainerAwareCommand
{
    /**
     * @var Generator
     */
    private $generator;

    protected function configure()
    {
        $this
            ->setName('isom:codemaker:create')
            ->setDescription('Creates a new Code Maker Generator.')
            ->setHelp('This command allows you to creates a new Code Maker Generator to your entity.');
//            ->setDefinition(array(
//                new InputArgument('displayName',
//                    InputArgument::REQUIRED, 'The simple name that you have mentioned in @displayName'),
//                new InputArgument('simplePattern',
//                    InputArgument::REQUIRED, 'The string pattern which will be applied on your entity CodeMaker ?'),
//                //new InputOption('inactive', null, InputOption::VALUE_NONE, 'Set the maker as inactive'),
//            ))
        $this
            ->addArgument('displayName', InputArgument::REQUIRED,
                'The simple name that you have mentioned in @displayName')
            ->addArgument('simplePattern', InputArgument::REQUIRED,
                'The string pattern which will be applied on your entity CodeMaker')
            ->addArgument('firstCode', InputArgument::REQUIRED,
                'The first Code')
            ->addArgument('respectPattern', InputArgument::REQUIRED,
                'The confirmation from user to respect this pattern for this entity (y/n)')
            ->addArgument('userValidate', InputArgument::REQUIRED,
            'The confirmation from user to respect this pattern for this entity (y/n)');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $displayName = $input->getArgument('displayName');
        $simplePattern = $input->getArgument('simplePattern');
        $respectPattern = $input->getArgument('respectPattern');
        $firstCode = $input->getArgument('firstCode');
        $userValidate = $input->getArgument('userValidate');

        $output->writeln(sprintf('
            Created Code Maker Generator to entity: <comment>%s</comment> with this following parameters :
            -Class name : <comment>%s</comment>
            -Simple pattern : <comment>%s</comment>
            -Preg pattern : <comment>%s</comment>
            -ID coding begin with : <comment>%s</comment>
            -Respect pattern : <comment>%s</comment>',
            $displayName,
            $this->generator->getClassName(),
            $simplePattern,
            $this->generator->getPregPattern(),
            $firstCode, $respectPattern));
    }


    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $this->generator = new Generator();
        $questions = array();

        if (!$input->getArgument('displayName')) {
            $question = new Question('Please write the <displayName value> that you have mentioned in <CodeMaker> annotation class: ');
            $question->setValidator(function ($displayName) {
                if (empty($displayName)) {
                    throw new \Exception('displayName value can not be empty !');
                }
                $className = $this->getContainer()->get('isom.code.maker')->check_annotation_conflict($displayName);
                $this->getContainer()->get('isom.code.maker')->check_generator_conflict($displayName);
                $this->generator->setClassName($className);
                $this->generator->setDisplayName($displayName);
                return $displayName;
            });
            $questions['displayName'] = $question;
        }

        if (!$input->getArgument('simplePattern')) {
            $question = new Question('Please write the "simplePattern value" : ');
            $output->writeln('If you don\'t know how to generate a simple pattern, visit this link');

            $question->setValidator(function ($simplePattern) {
                if (empty($simplePattern)) {
                    throw new \Exception('simplePattern can not be empty !');
                }
                if (false) {
                    throw new \Exception('simplePattern is not valid !');
                }
                /**
                 * Check if the SimplePattern is correct and Generate the pregX pattern
                 */
                $this->getContainer()->get('isom.code.maker')
                    ->check_simple_pattern($simplePattern);
                $this->generator->setSimplePattern($simplePattern);
                $pregPattern = $this->getContainer()->get('isom.code.maker')
                    ->generate_preg_pattern($this->generator->getSimplePattern());
                $this->generator->setPregPattern($pregPattern);
                return $simplePattern;
            });
            $questions['simplePattern'] = $question;
        }

        if (!$input->getArgument('respectPattern')) {
            $question = new Question('Do you want to respect this pattern ? (y/n): ');
            $question->setValidator(function ($respectPattern) {
                $respectPattern = strtolower($respectPattern);
                if ($respectPattern === "y" || $respectPattern === "n") {
                    $respectPattern = ($respectPattern === "y") ? true : false ;
                } else{
                    throw new \Exception('The response must be (\'y\' or \'n\') character !');
                }
                $this->generator->setRespectPattern($respectPattern);
                return $respectPattern;
            });

            $questions['respectPattern'] = $question;
        }

        if (!$input->getArgument('firstCode')) {
            $question = new Question('Please write your start (Code/Id) : ');
            $question->setValidator(function ($firstCode) {
                if (empty($firstCode)) {
                    throw new \Exception('start (Code/Id) can not be empty !');
                }
                /**
                 * Check if the correct ID
                 */
                $this->generator->setLastCode($firstCode);
                $this->getContainer()->get('isom.code.maker')->check_last_code($this->generator, true);
                //$this->getContainer()->get('isom.code.maker')->validate_generator($this->generator);
                return $firstCode;
            });
            $questions['firstCode'] = $question;
        }

        if (!$input->getArgument('userValidate')) {
            $question = new Question('Are you sure to create this generator (y/n): ');
            $question->setValidator(function ($userValidate) {

                $userValidate = strtolower($userValidate);
                if ($userValidate === "y" || $userValidate === "n") {
                    $userValidate = ($userValidate === "y") ? true : false ;
                    if($userValidate){
                        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
                        $em->persist($this->generator);
                        $em->flush();
                    }
                } else{
                    throw new \Exception('The response must be (\'y\' or \'n\') character !');
                }
                return $userValidate;
            });
            $questions['userValidate'] = $question;
        }

        foreach ($questions as $name => $question) {
            $answer = $this->getHelper('question')->ask($input, $output, $question);
            $input->setArgument($name, $answer);
        }
    }

}