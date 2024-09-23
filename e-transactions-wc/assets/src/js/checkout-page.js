import 'intl-tel-input/build/css/intlTelInput.css';
import '../scss/checkout-page.scss';

import intlTelInput from 'intl-tel-input';

const wceIntlTelInput = ( input ) => {

    intlTelInput( input, {
        initialCountry: 'fr',
        // showSelectedDialCode: true,
        // strictMode: true,
        allowDropdown: true,
        utilsScript: wc_etransactions.utils_path,
    });
}

const wceChangeTelInput = ( e, input, hideInput = true ) => {

    const inputTel     = window.intlTelInputGlobals.getInstance(input);
    const parent       = input.closest('.wce-number-input');
	const container    = input.closest('.wce-number');
    const valide       = parent.querySelector('.wce_up2pay_phone_valid');
    const invalide     = parent.querySelector('.wce_up2pay_phone_error');

    if ( inputTel.isValidNumber() ) {
        valide.classList.remove('wce-hide');
        invalide.classList.add('wce-hide');

        // create a hidden input and add it to parent element
        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = 'wce_up2pay_phone_number';
        hiddenInput.value = inputTel.getNumber();
        parent.appendChild( hiddenInput );

        const hiddenInputCountry = document.createElement('input');
        hiddenInputCountry.type = 'hidden';
        hiddenInputCountry.name = 'wce_up2pay_phone_country';
        hiddenInputCountry.value = inputTel.getSelectedCountryData().dialCode;
        parent.appendChild( hiddenInputCountry );

		if ( hideInput ) {
			container.style.display = 'none';
		}
    } else {
        valide.classList.add('wce-hide');
        invalide.classList.remove('wce-hide');

        // remove hidden input
        const hiddenInput = parent.querySelector('input[name="wce_up2pay_phone_number"]');
        if ( hiddenInput ) {
            parent.removeChild( hiddenInput );
        }

        const hiddenInputCountry = parent.querySelector('input[name="wce_up2pay_phone_country"]');
        if ( hiddenInputCountry ) {
            parent.removeChild( hiddenInputCountry );
        }
		container.style.removeProperty('display');
    }
}

window.wceIntlTelInput = wceIntlTelInput;
window.wceChangeTelInput = wceChangeTelInput;
