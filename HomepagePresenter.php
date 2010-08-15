<?php

/**
 * Homepage presenter.
 *
 * @author     John Doe
 * @package    MyApplication
 */
class HomepagePresenter extends BasePresenter
{

   public function startup() {
      parent::startup();
      
      // extend FormContainer to support CategoriesPicker
      \Nette\Forms\FormContainer::extensionMethod('addSuggestPicker', function (\Nette\Forms\FormContainer $_this, $name, $label = NULL, $items = NULL, $useKeys = TRUE) {
          return $_this[$name] = new \SuggestPicker\SuggestPicker($label, $items, $useKeys);
      }); 
   }



   public function createComponentForm() {
      $f = new \Nette\Application\AppForm($this, 'form');
                  
      $f->addSuggestPicker('pickerPouzeHodnoty', 'Picker používající pouze hodnoty', array('David', 'Lucie', 'Markéta', 'Radek', 'Ondra'), FALSE);
   
      $f->addSuggestPicker('pickerPouzeHodnotyPrefill', 'Picker s defaultní hodnotou používající pouze hodnoty', array('David', 'Lucie', 'Markéta', 'Radek', 'Ondra'), FALSE)
            ->setDefaultValue(array('David', 'Lucie')); // prefill se nastavuje hodnotou!
   
      $f->addSuggestPicker('pickerKliceHodnoty', 'Picker používající klíče i hodnoty', array('muz' => 'David', 'zena' => 'Lucie', 26 => 'Markéta', 30 => 'Radek', 'kamarad' => 'Ondra'));
      
      $f->addSuggestPicker('pickerKliceHodnotyPrefill', 'Picker s defaultní hodnotou používající klíče i hodnoty', array('muz' => 'David', 'zena' => 'Lucie', 26 => 'Markéta', 30 => 'Radek', 'kamarad' => 'Ondra'))
            ->setDefaultValue(array('muz', 26)); // prefill se nastavuje klicem!
            
      $f->addSuggestPicker('pickerKliceHodnotyAdd', 'Picker dovolující přidat vlastní hodnoty', array('muz' => 'David', 'zena' => 'Lucie', 26 => 'Markéta', 30 => 'Radek', 'kamarad' => 'Ondra'))
            ->setAddingAllowed(TRUE); // zatim spise formalita; pridane hodnoty je pri zpracovani nutne ziskat z $control->getAddedValues()
      
      $f->onSubmit[] = array($this, 'formSubmitted');
      
      $f->addSubmit('ok');
   }
   
   
   public function formSubmitted(\Nette\Application\AppForm $f) {
      $vals = $f->getValues();
      $this->template->out = \Nette\Debug::dump($vals, TRUE);
   }


	public function renderDefault()  {
		$this->template->message = 'We hope you enjoy this framework!';
	}
	
	
	

}
