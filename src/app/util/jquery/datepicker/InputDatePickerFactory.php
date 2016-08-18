<?php
namespace util\jquery\datepicker;

use n2n\web\ui\view\impl\html\HtmlElement;
use n2n\web\dispatch\map\PropertyPath;
use n2n\web\dispatch\ui\Form;
use util\jquery\datepicker\DatePickerUtils;
use n2n\web\ui\view\impl\html\HtmlUtils;
use n2n\reflection\CastUtils;
use n2n\web\dispatch\property\impl\DateTimeProperty;
use n2n\l10n\L10nUtils;
use n2n\web\dispatch\target\build\ParamHandler;

class InputDatePickerFactory {
	private $form;
	private $resolver;
	
	public function __construct(Form $form) {
		$this->form = $form;
		$this->resolver = $this->form->getMappingPathResolver();
	}
	
	public function create(PropertyPath $propertyPath, array $attrs = null) {
		$result = $this->resolver->analyze($propertyPath, array('n2n\web\dispatch\property\impl\DateTimeProperty'), false);
		$dateTimeProperty = $result->getManagedProperty();
		CastUtils::assertTrue($dateTimeProperty instanceof DateTimeProperty);
		
		$locale = $this->form->getView()->getN2nContext()->getN2nLocale();
		$dateStyle = $dateTimeProperty->getDateStyle();
		if ($dateStyle === null) {
			$dateStyle = L10nUtils::determineDateStyle($locale, true);
		}
		$timeStyle = $dateTimeProperty->getTimeStyle();
		if ($timeStyle === null) {
			$timeStyle = L10nUtils::determineTimeStyle($locale, true);
		}
		$icuPattern = $dateTimeProperty->getIcuPattern();
		
		$inputName = $this->form->getDispatchTargetEncoder()->buildValueParamName($propertyPath, false);
		
		$inputValue = null;
		if ($result->hasInvalidRawValue()) {
			$inputValue = $result->getInvalidRawValue();
		} else {
			$inputValue = $dateTimeProperty->convertMapValueToScalar($result->getMapValue(), $this->resolver->getN2nContext());
		}
		
		$elemAttrs = $this->form->enhanceElementAttrs(
				array('type' => 'text', 'name' => $inputName, 'value' => $inputValue), $propertyPath);
		
		$elemAttrs = HtmlUtils::mergeAttrs($elemAttrs, DatePickerUtils::getDatePickerOptionsFactory($locale)->createDatePickerOptions(
				DatePickerUtils::determinePattern($locale, $dateStyle, $timeStyle, null, $icuPattern))->buildHtmlAttrs());
		
		return new HtmlElement('input', HtmlUtils::mergeAttrs($elemAttrs, (array) $attrs));
	}
}
