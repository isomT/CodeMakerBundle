<?php
/**
 * Created by PhpStorm.
 * User: Omar ISMAIL
 * Date: 28/09/2017
 * Time: 16:11
 */

namespace SBC\CodeMakerBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use SBC\CodeMakerBundle\Annotation\CodeMaker;
use SBC\CodeMakerBundle\service\CodeMakerService;


class CodeMakerSubscriber implements EventSubscriber
{

    /**
     * @var CodeMakerService
     */
    private $codeMakerService;

    /**
     * @var
     */
    private $config;

    public function __construct(CodeMakerService $idMakerService)
    {
        $this->codeMakerService = $idMakerService;
    }

    public function setConfig( $config )
    {
        $this->config = $config;
    }

    public function getSubscribedEvents()
    {
        return array(
            'prePersist',
        );
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $object = $args->getObject();
        if($this->codeMakerService->get_annotation($object) instanceof CodeMaker){
            if($this->config['auto_update_id']){
                $this->codeMakerService->update_last_code($object);
            }
        }
    }
}