const account = () => {

    const toggleDemoMode                = document.querySelector('#WCE-JS-account-demo-mode');
    const tableNoDemoMode               = document.querySelector('#WCE-JS-account-table-no-demo');
    const tableDemoMode                 = document.querySelector('#WCE-JS-account-table-demo');
    const radioEnvironmentTest          = document.querySelector('#WCE-JS-account-environment-test');
    const radioEnvironmentProduction    = document.querySelector('#WCE-JS-account-environment-production');
    const trHmacKeyTest                 = document.querySelector('#WCE-JS-account-hmac-key-test');
    const trHmacKeyProduction           = document.querySelector('#WCE-JS-account-hmac-key-production');
    const checkboxExemption3DS          = document.querySelector('#WCE-JS-account-exemption3DS');
    const tableExemption3DS             = document.querySelector('#WCE-JS-account-table-exemption3DS');

    setDemoMode = () => {
        jQuery( tableDemoMode ).slideDown();
        jQuery( tableNoDemoMode ).hide();

        const inputs = tableNoDemoMode.querySelectorAll('input');
        inputs.forEach( input => {
            input.removeAttribute('required');
        });
    };

    removeDemoMode = () => {
        jQuery( tableDemoMode ).hide();
        jQuery( tableNoDemoMode ).slideDown();

        const inputs = tableNoDemoMode.querySelectorAll('input');
        inputs.forEach( input => {
            const tr = input.closest('tr');
            if ( tr ) {
                if ( tr.classList.contains('required') ) {
                    input.setAttribute('required', 'required');
                }
            }
        });
    };

    setTest = () => {
        
        if ( trHmacKeyTest ) {
            trHmacKeyTest.classList.remove('opacity');
            trHmacKeyTest.classList.add('required');

            const input = trHmacKeyTest.querySelector('input');
            if ( input ) {
                input.setAttribute('required', 'required');
            }
        }

        if ( trHmacKeyProduction ) {
            trHmacKeyProduction.classList.add('opacity');
            trHmacKeyProduction.classList.remove('required');

            const input = trHmacKeyProduction.querySelector('input');
            if ( input ) {
                input.removeAttribute('required');
            }
        }
    }

    setProduction = () => {

        if ( trHmacKeyTest ) {
            trHmacKeyTest.classList.add('opacity');
            trHmacKeyTest.classList.remove('required');

            const input = trHmacKeyTest.querySelector('input');
            if ( input ) {
                input.removeAttribute('required');
            }
        }

        if ( trHmacKeyProduction ) {
            trHmacKeyProduction.classList.remove('opacity');
            trHmacKeyProduction.classList.add('required');

            const input = trHmacKeyProduction.querySelector('input');
            if ( input ) {
                input.setAttribute('required', 'required');
            }
        }
    }   

    if ( toggleDemoMode ) {
        toggleDemoMode.addEventListener( 'change', () => {
            
            if ( toggleDemoMode.checked ) {
                setDemoMode();
            } else {
                removeDemoMode();
            }
        });
    }

    if ( radioEnvironmentTest ) {
        radioEnvironmentTest.addEventListener( 'change', () => {
            setTest();
        });
    }

    if ( radioEnvironmentProduction ) {
        radioEnvironmentProduction.addEventListener( 'change', () => {
            setProduction();
        });
    }

    if ( checkboxExemption3DS ) {
        checkboxExemption3DS.addEventListener( 'change', () => {
            if ( checkboxExemption3DS.checked ) {
                jQuery( tableExemption3DS ).slideDown();
            } else {
                jQuery( tableExemption3DS ).hide();
            }
        });
    }

};
document.addEventListener( 'DOMContentLoaded', () => account() );