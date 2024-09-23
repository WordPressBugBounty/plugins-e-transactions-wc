import IntlTelInput from 'intl-tel-input';

const settings        = window.wc.wcSettings.getSetting( 'wc_etransctions_data', {} );
const methods         = settings.methods;
const { useState, useRef, useEffect }    = wp.element;
const { sprintf }    = wp.i18n;

/**
 * Updates the payment method data with the phone number and country code from the provided IntlTelInput.
 */
function changeTelInput( target, props, iti, paymentMethodData, setpaymentMethodData, hideInput = true ) {

    const parent    = target.closest('.wce-number-input');
    const container = target.closest('.wce-number');
    const valide    = parent.querySelector('.wce_up2pay_phone_valid');
    const invalide  = parent.querySelector('.wce_up2pay_phone_error');

    if ( iti.isValidNumber() ) {
        valide.classList.remove('wce-hide');
        invalide.classList.add('wce-hide');

        paymentMethodData['wce_up2pay_phone_number'] = iti.getNumber();
        paymentMethodData['wce_up2pay_phone_country'] = iti.getSelectedCountryData().dialCode;

		if ( hideInput ) {
			container.style.display = 'none';
		}
    } else {
        valide.classList.add('wce-hide');
        invalide.classList.remove('wce-hide');

        paymentMethodData['wce_up2pay_phone_number'] = '';
        paymentMethodData['wce_up2pay_phone_country'] = '';
		container.style.removeProperty( 'display' );
    }

    setpaymentMethodData( paymentMethodData );

    const { eventRegistration, emitResponse, method } = props;
    const { onPaymentSetup } = eventRegistration;

    onPaymentSetup( async () => {

        return {
            type: emitResponse.responseTypes.SUCCESS,
            meta: {
                paymentMethodData: paymentMethodData
            },
        }}
    );
}

/**
 * Updates the payment method data with the value of the one-click checkbox and triggers the payment setup.
 */
function OneClickChanged( e, props, paymentMethodData, setpaymentMethodData ) {

    const isChecked = e.target.checked;
    
    let one_click = '0';
    if ( isChecked ) {
        one_click = '1';
    }

    const { eventRegistration, emitResponse, method } = props;
    const { onPaymentSetup } = eventRegistration;

    paymentMethodData['wce_one_click'] = one_click;
    setpaymentMethodData( paymentMethodData );

    onPaymentSetup( async () => {
        return {
            type: emitResponse.responseTypes.SUCCESS,
            meta: {
                paymentMethodData: paymentMethodData
            },
        }}
    );
}

/**
 * Renders a label component with a payment method label and an icon.
 */
const Label = ( props ) => {

    const { method } = props;
    const { label, icon } = method;

    return (
        <span className='wc-block-payment-method__label' style={{ width: '100%' }}>
            {label}
            <img src={icon} alt={label} className='wc-block-payment-method__image' style={{ float: 'right', marginRight: '20px' }} />
        </span>
    );
}

/**
 * Renders a notice indicating the current environment of the Up2Pay payment gateway.
 */
const Environment = () => {

    let mode = 'DEMO';
    if ( wc_etransactions.account_demo_mode === '0' ) {

        if ( 'production' === wc_etransactions.account_environment ) {
            mode = false;
        } else {
            mode = 'TEST';
        }
    }

    if ( mode ) {
        return (
            <div className="wce-description-notice wce-notice-warning wce-notice-padding">
                <span> { sprintf( wc_etransactions.i18n.environment, mode ) }</span>
            </div>
        )
    }
    
    return '';
}

/**
 * Renders a component for entering a phone number.
 */
const Number = ( { props, paymentMethodData, setpaymentMethodData, iti, setIti } ) => {

	const billingPhone = props.billing.billingAddress.phone;
    const inputRef     = useRef(null);

    useEffect(() => {
        let int = IntlTelInput(inputRef.current, {
            initialCountry: 'fr',
            // showSelectedDialCode: true,
            // strictMode: true,
            allowDropdown: true,
            utilsScript: wc_etransactions.utils_path,
        });

        setIti( int );

		setTimeout(() => {
			changeTelInput( inputRef.current, props, int, paymentMethodData, setpaymentMethodData );
		}, 400);
    }, [billingPhone]);

    return (
        <div className="wce-number" style={{ display: 'none' }}>
            <div className="wce-number-notice wce-notice-warning wce-notice-padding">
                <span>{ wc_etransactions.i18n.enterNumber }</span>
            </div>
            <div className="wce-number-input">
            <input type="phone" ref={inputRef} defaultValue={billingPhone} onKeyUp={ (e) => changeTelInput( e.target, props, iti, paymentMethodData, setpaymentMethodData, false ) } />
                <img className="wce_up2pay_phone_valid wce-hide" src={ wc_etransactions.plugin_url + "assets/img/icons/icon_valid.png" }/>
                <span className="wce_up2pay_phone_error">{ wc_etransactions.i18n.validNumber }</span>
            </div>
        </div>
    )
}

/**
 * Renders a one-click notice if one-click is enabled in the params.
 */
const OneClick = ( { props, paymentMethodData, setpaymentMethodData } ) => {

    const { eventRegistration, emitResponse, method } = props;
    const { params } = method;

    if ( params.one_click_enabled === '1' ) {
        return (
            <div className="wce-one-click-notice wce-notice-padding">
                <label for="wce_one_click">
                    <input type="checkbox" name="wce_one_click" id="wce_one_click" value="1" onChange={ (e) => OneClickChanged(e, props, paymentMethodData, setpaymentMethodData)} />
                    <span>{ wc_etransactions.i18n.oneClick }</span>
                </label>
            </div>
        )
    }

    return '';
}

/**
 * Renders the description component for a payment method.
 */
const Description = ( props ) => {

    const [paymentMethodData, setpaymentMethodData] = useState( {} );
    const [iti, setIti] = useState( null );

    return (
        <>
            <Environment />
            <Number props={props} paymentMethodData={paymentMethodData} setpaymentMethodData={setpaymentMethodData} iti={iti} setIti={setIti} />
            <OneClick props={props} paymentMethodData={paymentMethodData} setpaymentMethodData={setpaymentMethodData} />
        </>
    );
}

methods.forEach( method => {

	const params = {
		name: method.name,
		label: <Label method={method} />,
		content: <Description method={method} />,
		edit: <Description method={method} />,
		canMakePayment: () => true,
		ariaLabel: method.label,
        supports: {}
	};
	window.wc.wcBlocksRegistry.registerPaymentMethod( params );
});
