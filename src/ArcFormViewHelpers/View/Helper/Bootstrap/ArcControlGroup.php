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
    );
    protected $checkbox = array(
        'Zend\Form\Element\Checkbox',
        //'Zend\Form\Element\MultiCheckbox',
    );

    /**
     * @param \Zend\Form\Element $element
     * @param string             $typeOverride
     * @return string
     * @todo Fix for multi checkbox elements where only one label will be rendered
     */
    public function __invoke(Element $element, $typeOverride = '')
    {
        //Get the element's default view helper from the beginning to shortcut elements that don't have a control group
        $helper = $this->getViewHelper($element, $typeOverride);

        //These elements don't have a control group, just return their renders
        if ($element instanceof Element\Hidden || $element instanceof Element\Csrf || $element instanceof Element\Collection) {
            return $helper->render($element);
        }

        $label = '';
        $elementHtml = '';

        //Prepare the label mark up by checking if the class attribute
        //is defined & if it contains the control-label class if it's not a radio or checkbox
        $labelAttributes = $element->getLabelAttributes();
        if (!$labelAttributes || !array_key_exists('class', $labelAttributes)) {
            $labelAttributes['class'] = '';
        }
        //Only add the class to the label if it is one of the control elements
        if (in_array(get_class($element), $this->control)) {
            $labelAttributes['class'] .= strstr(
                $labelAttributes['class'],
                'control-label'
            ) === false ? ' control-label' : '';
            $element->setLabelAttributes($labelAttributes);
            $label = $this->getView()->formLabel($element);
            $elementHtml = $helper->render($element);
        } else {
            if (in_array(get_class($element), $this->checkbox)) {
                $labelAttributes['class'] .= strstr($labelAttributes['class'], 'checkbox') === false ? ' checkbox' : '';
                $element->setLabelAttributes($labelAttributes);
                $elementHtml = $this->getView()->formLabel($element, $helper->render($element), FormLabel::APPEND);
            }
        }

        $decorator = new ViewModel(array('label' => $label, 'element' => $elementHtml));
        $decorator->setTemplate('arc/bootstrap/control-group');

        return $this->getView()->render($decorator);
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
        return $helper;
    }
}