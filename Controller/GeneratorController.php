<?php

namespace SBC\CodeMakerBundle\Controller;

use SBC\CodeMakerBundle\Entity\Generator;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * Generator controller.
 *
 * @Route("code_maker")
 */
class GeneratorController extends Controller
{

    /**
     * @Route("/", name="generator_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $generators = $em->getRepository('CodeMakerBundle:Generator')->findAll();

        return $this->render('@CodeMaker/generator/index.html.twig', array(
            'generators' => $generators,
        ));
    }

    /**
     * @Route("/new", name="generator_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $generator = new Generator();
        $availableList = array();
        $entities_with_maker = $this->get('isom.code.maker')->get_entities_with_maker();

        foreach ($entities_with_maker as $item){
            $annotation = $item['makerAnnotation'];

//            $displayName = $annotation->displayName;
//            $this->get('isom.code.maker')->check_annotation_conflict($displayName);
//            $find = $this->get('isom.code.maker')->get_generator($annotation);
//            if($find === null){
//                $availableList[$displayName] = $displayName;
//            }

            if($annotation->discriminatorColumn === null){
                $displayName = $annotation->displayName;
                $this->get('isom.code.maker')->check_annotation_conflict($displayName);
                $find = $this->get('isom.code.maker')->get_generator($annotation);
                if($find === null){
                    $availableList[$displayName] = $displayName;
                }
            } else {
                $discriminations = $annotation->discriminations;
                foreach ($discriminations as $discriminationColumnAnnotation){
                    $displayName = $discriminationColumnAnnotation->displayName;
                    $discriminationValue = $discriminationColumnAnnotation->value;
                    $this->get('isom.code.maker')->check_annotation_conflict($displayName);
                    $find = $this->get('isom.code.maker')->get_generator($annotation, $discriminationValue);
                    if($find === null){
                        $availableList[$displayName] = $displayName;
                    }
                }
            }
        }

        $form = $this->createForm('SBC\CodeMakerBundle\Form\GeneratorType', $generator, array(
            'entities_name' => $availableList
        ));

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('isom.code.maker')->validate_generator($generator);
            $em->persist($generator);
            $em->flush();
            return $this->redirectToRoute('generator_index');
        }
        return $this->render('@CodeMaker/generator/new.html.twig', array(
            'generator' => $generator,
            'form' => $form->createView(),
            'cm_form_template' => $this->getParameter('cm_form_template')
        ));
    }

    /**
     * @Route("/{id}", name="generator_delete")
     * @Method({"GET", "POST", "DELETE"})
     */
    public function deleteAction(Generator $generator)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($generator);
        $em->flush();
        return $this->redirectToRoute('generator_index');
    }

}
