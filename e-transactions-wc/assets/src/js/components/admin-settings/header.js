const header = () => {

    const supportPopupContainer = document.querySelector( '#JS-WCE-header-support-popup' );
    const supportBtnOpenPopup   = document.querySelector( '#JS-WCE-header-support-open' );
    const supportBtnClosePopup  = document.querySelector( '#JS-WCE-header-support-close' );
    const supportOverlay        = document.querySelector( '#JS-WCE-header-support-overlay' );

    const configPopupContainer  = document.querySelector( '#JS-WCE-header-config-popup' );
    const configBtnOpenPopup    = document.querySelector( '#JS-WCE-header-config-open' );
    const configBtnClosePopup   = document.querySelector( '#JS-WCE-header-config-close' );
    const configOverlay         = document.querySelector( '#JS-WCE-header-config-overlay' );

    const textareaCode          = document.querySelector( '#JS-WCE-header-textarea-code' );

    function openSupportPopup() {
        supportPopupContainer.classList.add( 'show' );
    }

    function closeSupportPopup() {
        supportPopupContainer.classList.remove( 'show' );
    }

    function openConfigPopup() {
        configPopupContainer.classList.add( 'show' );
    }

    function closeConfigPopup() {
        configPopupContainer.classList.remove( 'show' );
    }

    function showMessage() {

        const parent = textareaCode.parentElement;
        parent.classList.add( 'show' );

        setTimeout( () => {
            parent.classList.remove( 'show' );
        }, 3000 );
    }

    if ( supportBtnOpenPopup ) {
        supportBtnOpenPopup.addEventListener( 'click', () => {
            openSupportPopup();
        });
    }

    if ( supportBtnClosePopup ) {
        supportBtnClosePopup.addEventListener( 'click', () => {
            closeSupportPopup();
        });
    }

    if ( supportOverlay ) {
        supportOverlay.addEventListener( 'click', () => {
            closeSupportPopup();
        });
    }

    if ( configBtnOpenPopup ) {
        configBtnOpenPopup.addEventListener( 'click', () => {
            openConfigPopup();
        });
    }

    if ( configBtnClosePopup ) {
        configBtnClosePopup.addEventListener( 'click', () => {
            closeConfigPopup();
        });
    }

    if ( configOverlay ) {
        configOverlay.addEventListener( 'click', () => {
            closeConfigPopup();
        });
    }

    if ( textareaCode ) {
        textareaCode.addEventListener( 'click', () => {

            if ( navigator.clipboard ) {

                const textToCopy = textareaCode.value;
                navigator.clipboard.writeText(textToCopy)
                .then( () => {
                    showMessage();
                });

            } else {

                textareaCode.select();
                document.execCommand('copy');
                textareaCode.selectionEnd = 0;   
            }
        });
    }

};
document.addEventListener( 'DOMContentLoaded', () => header() );
