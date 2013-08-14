<?php
namespace ApiModule\Model;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;
use ApiModule\Model\TimeStampedEntity;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;

/**
 * Log entity
 * 
 * @category ApiModule
 * @package Model
 * @author  Elton Minetto<eminetto@coderockr.com>
 * @author  Mateus Guerra<mateus@coderockr.com>
 *
 * @ORM\Entity
 * @ORM\Table(name="ApiLog")
 */
class Log extends TimeStampedEntity
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer");
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @Groups({"ApiModule\Model\Log"})
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     * 
     * @Groups({"ApiModule\Model\Log"})
     */
    protected $resource;

    /**
     * @ORM\ManyToOne(targetEntity="Token", inversedBy="logCollection", cascade={"persist", "merge", "refresh"})
     * 
     * @var Token
     */
    protected $token;

    /**
     * Configuration of the Entity's filters
     *
     * @return Zend\InputFilter\InputFilter
     */
    public function getInputFilter()
    {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();
            $factory     = new InputFactory();

            $inputFilter->add($factory->createInput(array(
                'name'     => 'resource',
                'required' => true,
                'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name'    => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 1,
                            'max'      => 100,
                        ),
                    ),
                ),
            )));
            
            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }
}