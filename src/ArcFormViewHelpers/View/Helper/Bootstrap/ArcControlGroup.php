<?php
/**
 * Formats a Zend\Form\Element in a Twitter Bootstrap control group
 *
 * @link http://twitter.github.io/bootstrap/base-css.html#forms
 */

namespace ArcFormViewHelpers\View\Helper\Bootstrap;


use Zend\Form\Element;
use Zend\Form\View\Helper\FormLabel;
use Zend\View\Helper\AbstractHelper;
use Zend\View\Model\ViewModel;

class ArcControlGroup extends AbstractHelper
{
    protected $control = array(
        'Zend\Form\Element\Captcha',
        'Zend\Form\Element\Color',
        'Zend\Form\Element\Date',
        'Zend\Form\Element\DateTime',
        'Zend\Form\Element\DateTimeLocal',
        'Zend\Form\Element\DateTimeSelect',
        'Zend\Form\Element\Email',
        'Zend\Form\Element\File',
        'Zend\Form\Element\Image',
        'Zend\Form\Element\Month',
        'Zend\Form\Element\MonthSelect',
        'Zend\Form\Element\Number',
        'Zend\Form\Element\Password',
        'Zend\Form\Element\Range',
        'Zend\Form\Element\Select',
        'Zend\Form\Element\Text',
        'Zend\Form\Element\Textarea',
        'Zend\Form\Element\Time',
        'Zend\Form\Element\Url',
        'Zend\Form\Element\Week',
    );
    protected $radio = array(
        'Zend\Form\Element\Radio',
        //Treat multi-checkboxes like radios
        'Zend\Form\Element\MultiCheckbox',
    );
    protected $checkbox = array(
        'Zend\Form\Element\Checkbox',
    );

    /**
     * @param \Zend\Form\Element $element
     * @param string             $typeOverride
     * @return string
     */
    public function __invoke(Element $element, $typeOverride = '')
    {
        if (is_string($typeOverride) && !empty($typeOverride)) {
            $factory = new \Zend\Form\Factory();
            $element = $factory->createElement(
                array(
                    'name' => $element->getName(),
                    'type' => $typeOverride,
                    'value' => $element->getValue(),
                    'attributes' => $element->getAttributes(),
                    'options' => $element->getOptions(),
                )
            );
        }
        $decorator = new ViewModel(array(
            'label' => $this->renderLabel($element),
            'element' => $this->renderElement($element)
        ));
        $decorator->setTemplate('arc/bootstrap/control-group');

        return $this->getView()->render($decorator);
    }

    /**
     * Renders the control label for the Bootstrap control group
     *
     * @param Element $element
     * @return string
     */
    public function renderLabel(Element $element)
    {
        $label = '';

        //Get the right class
        $element->setLabelAttributes($this->setLabelClasses($element));

        //Only add the class to the label if it is one of the control elements
        if ($this->isControl($element) || $this->isRadio($element)) {
            $label = $this->getView()->formLabel($element);
        }

        //Only control elements, multi-checkboxes, & radios have a label that appears outside of the control group

        return $label;
    }

    /**
     * Get the proper class for the label depending on the element type.
     * NOTE: This returns all the attributes for the element, not just the class
     *
     * @param Element $element
     * @return array
     */
    public function setLabelClasses(Element $element)
    {
        $labelAttributes = $element->getLabelAttributes();
        $controlClass = '';

        //If the class attribute is not set, set an empty one to avoid errors later
        if (!isset($labelAttributes['class'])) {
            $labelAttributes['class'] = '';
        }

        //Radio elements are special. The group of elements can have a control label & each individual element can have
        //it's own label. So we set the control label here and use
        if ($this->isControl($element) || $this->isRadio($element)) {
            $controlClass = strstr($labelAttributes['class'], 'control-label') === false ? ' control-label' : '';
        } elseif ($this->isCheckbox($element)) {
            $controlClass = strstr($labelAttributes['class'], 'checkbox') === false ? ' checkbox' : '';
        }

        //Hidden elements don't have special classes in Bootstrap so no need to check for them

        $labelAttributes['class'] = trim($labelAttributes['class'] . $controlClass);

        return $labelAttributes;
    }

    /**
     * @param Element $element
     * @return bool
     */
    public function isControl(Element $element)
    {
        return in_array(get_class($element), $this->control);
    }

    /**
     * @param Element $element
     * @return bool
     */
    public function isRadio(Element $element)
    {
        return in_array(get_class($element), $this->radio);
    }

    /**
     * @param Element $element
     * @return bool
     */
    public function isCheckbox(Element $element)
    {
        return in_array(get_class($element), $this->checkbox);
    }

    /**
     * @param Element $element
     * @return mixed
     */
    public function renderElement(Element $element)
    {
        $helper = $this->getViewHelper($element);
        if ($this->isCheckbox($element)) {
            $elementHtml = $this->getView()->formLabel($element, $helper->render($element), FormLabel::APPEND);
        } elseif($this->isRadio($element)){
            $element->setValueOptions($this->setOptionLabelAttributes($element));
            $elementHtml = $helper->render($element);
        } else {
            $elementHtml = $helper->render($element);
        }

        return $elementHtml;
    }

    /**
     * @param \Zend\Form\Element $element
     * @param string             $typeOverride
     * @return \Zend\Form\View\Helper\AbstractHelper
     */
    protected function getViewHelper(Element $element, $typeOverride = '')
    {
        $class = '\Zend\Form\View\Helper\Form';
        if (isset($typeOverride) && !empty($typeOverride)) {
            $class .= $typeOverride;
        } else {
            $class .= array_pop(explode('\\', get_class($element)));
        }
        if (class_exists($class, true)) {
            $helper = new $class;
        } else {
            $helper = new \Zend\Form\View\Helper\FormText();
        }
        $helper->setView($this->getView());
        return $helper;
    }

    /**
     * Iterates through the value_options & set the value option's appropriate class
     *
     * @param Element\MultiCheckbox $element
     * @return array
     */
    public function setOptionLabelAttributes(Element\MultiCheckbox $element)
    {
        $valueOptions = $element->getValueOptions();
        $elementClass = array_pop(explode('\\', get_class($element)));
        $labelClass = '';

        foreach ($valueOptions as $key => $spec) {
            if (!is_array($spec)) {
                continue;
            }

            if(!isset($spec['label_attributes'])) {
                $spec['label_attributes'] = array('class' => '');
            }

            if (is_scalar($spec['label_attributes'])) {
                $spec['label_attributes'] = array($spec['label_attributes']);
            }

            if (!isset($spec['label_attributes']['class'])) {
                $spec['label_attributes']['class'] = '';
            }

            switch ($elementClass) {
                case 'MultiCheckbox':
                    $labelClass = 'checkbox';
                    break;
                case 'Radio':
                    $labelClass = 'radio';
                    break;
            }
            $class = strstr($spec['label_attributes']['class'], $labelClass) === false ? ' ' . $labelClass : '';
            $spec['label_attributes']['class'] = trim($spec['label_attributes']['class'] . $class);
            $valueOptions[$key] = $spec;
        }

        return $valueOptions;
    }

    /**
     * @param Element $element
     * @return bool
     */
    public function isHidden(Element $element)
    {
        return $element instanceof Element\Hidden || $element instanceof Element\Csrf || $element instanceof Element\Collection;
    }
}