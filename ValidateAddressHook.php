<?php

/*
 * Contact Formular Check Extension for Contao
 * Copyright (c) 2014, Falko Schumann <http://www.muspellheim.de>
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 *  - Redistributions of source code must retain the above copyright notice,
 *    this list of conditions and the following disclaimer.
 *  - Redistributions in binary form must reproduce the above copyright notice,
 *    this list of conditions and the following disclaimer in the documentation
 *    and/or other materials  provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */


/**
 * Implementation of validateFormField hook.
 *
 * This hook expects the form fields <code>postalCode</code>, <code>city</code> and <code>country</code> and verify
 * user input of this fields withs http://www.geonames.org.
 *
 * @copyright  Falko Schumann 2014
 * @author     Falko Schumann <http://www.muspellheim.de>
 * @package    ContactFormularCheck
 * @license    BSD-2-Clause <http://opensource.org/licenses/BSD-2-Clause>
 */
class ValidateAddressHook extends Frontend
{

    var $formIds = array('auto_form_1', 'auto_form_2', 'auto_form_3', 'auto_form_4', 'auto_form_6');
    var $widgetNameForPostalCode = 'postal_code';
    var $widgetNameForCity = 'city';
    var $widgetNameForCountry = 'country';
    var $username = 'foobar';

    public function validateAddressField(Widget $objWidget, $formId)
    {
        if (!in_array($formId, $this->formIds))
        {
            return $objWidget;
        }

        switch ($objWidget->name)
        {
            case $this->widgetNameForPostalCode:
                $postal_code = $objWidget->value;
                $city = $this->Input->post($this->widgetNameForCity);
                $country = $this->Input->post($this->widgetNameForCountry);
                if (!$this->validateAddress($postal_code, $city, $country))
                {
                    $this->log("Form $formId validate postal code failed: postal_code=$postal_code, city=$city, country=$country", __CLASS__ . '::' . __FUNCTION__, TL_GENERAL);
                    $objWidget->addError($GLOBALS['TL_LANG']['ERR']['validationErrorPostalCode']);
                }
                break;
            case $this->widgetNameForCity:
                $postal_code = $this->Input->post($this->widgetNameForPostalCode);
                $city = $objWidget->value;
                $country = $this->Input->post($this->widgetNameForCountry);
                if (!$this->validateAddress($postal_code, $city, $country))
                {
                    $this->log("Form $formId Validate city failed: postal_code=$postal_code, city=$city, country=$country", __CLASS__ . '::' . __FUNCTION__, TL_GENERAL);
                    $objWidget->addError($GLOBALS['TL_LANG']['ERR']['validationErrorCity']);
                }
                break;
            case $this->widgetNameForCountry:
                $postal_code = $this->Input->post($this->widgetNameForPostalCode);
                $city = $this->Input->post($this->widgetNameForCity);
                $country = $objWidget->value;
                if (!$this->validateAddress($postal_code, $city, $country))
                {
                    $this->log("Form $formId Validate country failed: postal_code=$postal_code, city=$city, country=$country", __CLASS__ . '::' . __FUNCTION__, TL_GENERAL);
                    $objWidget->addError($GLOBALS['TL_LANG']['ERR']['validationErrorCountry']);
                }
                break;
        }

        return $objWidget;
    }

    private function validateAddress($postal_code, $city, $country)
    {
        $this->log('Current language is ' . $GLOBALS['TL_LANGUAGE'], __CLASS__ . '::' . __FUNCTION__, TL_GENERAL);


        $requestUrl = 'http://api.geonames.org/postalCodeSearchJSON?postalcode=' . $postal_code . '&placename=' . $city . '&country=' . $country . '&username=' . $this->username;
        $answerString = file_get_contents($requestUrl);
        $answerJson = json_decode($answerString, true);
        if (array_key_exists('postalCodes', $answerJson)) {
            return sizeof($answerJson['postalCodes']) > 0;
        }

        if (array_key_exists('status', $answerJson)) {
            $this->log('Validating address failed: ' . $answerJson['status']['message'], __CLASS__ . '::' . __FUNCTION__, TL_GENERAL);
        }

        // fallback
        return true;
    }

}
