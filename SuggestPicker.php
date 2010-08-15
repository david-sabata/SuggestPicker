<?php

namespace SuggestPicker;

use \Nette\Web\Html;
use \Nette\Templates\Template;

/**
 * Multiple items picker with suggestion
 *
 * @copyright Copyright (c) 2009, 2010 David Šabata
 */
class SuggestPicker extends \Nette\Forms\FormControl {

   /** @var array $items always a value=>title array; $useKeys is respected in getters/setters */
   protected $items = array();

   /** @var array $value always a value=>title array; $useKeys is respected in getters/setters */
   protected $value = array();

   /** @var array $addedValues values entered by user which are not present in $items; only when $allowAdding=TRUE */
   protected $addedValues = array();


   /** @var string $startText text to display when the input field is empty */
   public $startText = '';

   /** @var string $emptyText text to display when there are no search results */
   public $emptyText = "No Results";

   /** @var string $limitText text to display when the number of selection has reached it's limit */
   public $limitText = "No More Selections Are Allowed";

   /** @var int|FALSE $selectionLimit max number of selected items */
   public $selectionLimit = FALSE; // BUG: when limit's reached, autoSuggest only stop suggesting but does not prevent adding new values

   /** @var bool $matchCase match case when suggesting */
   public $matchCase = FALSE;

   
   /** @var bool $supportFilesIncluded has support (js,css) files already been included? */
   private static $supportFilesIncluded = FALSE;

   /**
    * If allowed users can type in new values, these can be retrieved by $control->getAddedValues().
    * Otherwise the values are ignored from user.
    * Setting invalid keys/values with $control->setValue() never minds this setting!
    * @var bool $allowAdding
    */
   protected $allowAdding = TRUE;

   /**
    * FALSE for getting/setting/defining/showing items as values,
    * TRUE for getting/setting keys, defining keys=>values and showing values
    * @var bool $useKeys
    */
   protected $useKeys = TRUE;
   

   /**
    * @param mixed $label
    * @param array $items
    * @param bool $useKeys
    * @return SuggestPicker
    */
   public function __construct($label = NULL, array $items = NULL, $useKeys = TRUE) {
      parent::__construct($label);

      $this->useKeys = $useKeys;

      if ($items !== NULL)
         $this->setItems($items);
   }

   /**
    * Allow or deny adding new values by user
    * @param bool $val
    * @return SuggestPicker
    */
   public function setAddingAllowed($val) {
      $this->allowAdding = (bool)$val;
      return $this;
   }

   /**
    * @return bool
    */
   public function isAddingAllowed() {
      return $this->allowAdding;
   }

   /**
    * @return array
    */
   public function getAddedValues() {
      return $this->addedValues;
   }

   /**
    * Sets items from which to choose
    * @param array $items
    * @return SuggestPicker
    */
   public function setItems(array $items) {
      $this->items = $items;     
      
      foreach ($items as $key => $value) {
         if (!is_scalar($value)) {
            throw new \InvalidArgumentException("All items must be scalars.");
         }
      }
      
      return $this;
   }

   /**
    * Returns items from which to choose
    * @return array
    */
   public function getItems() {
      return $this->items;
   }

   /**
    * Returns items in form of array of arrays('value'=>key, 'title'=>value)
    * @return array
    */
   protected function formatItems(array $items) {
      $new = array();

      foreach ($items as $k => $v)
         $new[] = array('value' => $k, 'title' => $v);

      return $new;
   }


   /**
    * @return Html
    */
   public function getControl() {
      $box = Html::el();
      $box->add( parent::getControl() );

      // add JS functionality
      $template = new Template(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'SuggestPicker.phtml');
      $template->registerFilter(new \Nette\Templates\LatteFilter());
      $template->data = $this->formatItems( $this->getItems() );
      $template->preFill = $this->formatItems( $this->value );
      $template->htmlId = $this->getHtmlId();
      $template->startText = $this->startText;
      $template->emptyText = $this->emptyText;
      $template->limitText = $this->limitText;
      $template->selectionLimit = $this->selectionLimit;
      $template->matchCase = $this->matchCase;
      
      $template->incl = !self::$supportFilesIncluded;
      self::$supportFilesIncluded = TRUE;

      $box->add((string)$template);

      return $box;
   }


   /**
    * Respects $control->useKeys when setting an array
    * @param mixed $value array or comma separated keys string
    * @return SuggestPicker
    */
   public function setValue($value) {

      /** @var bool $forceUsingKeys used to bypass the $useKey settings; autoSuggest plugin returns always keys */
      $forceUsingKeys = FALSE;

      // comma separated keys string support - needed by autoSuggest script
      // BUG: autoSuggest doesn't make a difference between selected key and newly added value
      if (!is_array($value) && $this->getForm() && $this->getForm()->isSubmitted()) {
         $resultKeys = explode(',', trim($value, ','));
         $forceUsingKeys = TRUE;
         $value = array();
         foreach($resultKeys as $key)
            $value[] = trim($key);
      }

      // setValue is called with array of keys to be set (values are searched in $control->items)
      if ($this->useKeys || $forceUsingKeys) {
         $keys = $value;

         foreach ($keys as $key) {
            if ( isset($this->items[$key]) )
               $this->value[$key] = $this->items[$key];
            elseif ($forceUsingKeys && $this->isAddingAllowed())
               $this->addedValues[] = $key;
            elseif (!$forceUsingKeys)
               throw new \InvalidArgumentException("Can't set value '$key', key is not set in control items.");               
         }
         
      }
      // setValue is called with array of values to be set (keys are searched in $control->items)
      else {
         $values = $value;
         
         foreach ($values as $val) {
            if ( ($key = array_search($val, $this->items)) !== FALSE )
               $this->value[$key] = $val;
            else
               throw new \InvalidArgumentException("Can't set value '$val', value is not present in control items.");
               
         }
      }        
      
      return $this;
   }


   /**
    * Respects $control->useKeys
    * @return array
    */
   public function getValue() {
      return $this->useKeys ? array_keys($this->value) : array_values($this->value);
   }


   
}
?>
