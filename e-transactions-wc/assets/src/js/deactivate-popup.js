import '../scss/deactivate-popup.scss';

const popup = () => {

    const popupContainer  = document.querySelector( '#JS-WCE-deactivate-popup' );
    const overlay         = document.querySelector( '#JS-WCE-deactivate-popup-overlay' );
    const btnOpenPopup    = document.querySelector( '#deactivate-e-transactions-wc' );
    const btnClosePopup   = document.querySelector( '#JS-WCE-deactivate-popup-close' );
    const btnCancelPopup  = document.querySelector( '#JS-WCE-deactivate-popup-cancel' );
    const btnDeactivate   = document.querySelector( '#JS-WCE-deactivate-popup-deactivate' );

    function addHrefToDeactivateBtn() {
        let href = btnOpenPopup.getAttribute( 'href' );

        if ( btnDeactivate ) {
            btnDeactivate.setAttribute( 'href', href );
        }
    }
    addHrefToDeactivateBtn();

    function openPopup() {
        popupContainer.classList.add( 'show' );
    }

    function closePopup() {
        popupContainer.classList.remove( 'show' );
    }

    if ( btnOpenPopup ) {
        btnOpenPopup.addEventListener( 'click', (e) => {
            e.preventDefault();
            openPopup();
        });
    }

    if ( btnClosePopup ) {
        btnClosePopup.addEventListener( 'click', (e) => {
            e.preventDefault();
            closePopup();
        });
    }

    if ( btnCancelPopup ) {
        btnCancelPopup.addEventListener( 'click', (e) => {
            e.preventDefault();
            closePopup();
        });
    }

    if ( overlay ) {
        overlay.addEventListener( 'click', (e) => {
            e.preventDefault();
            closePopup();
        });
    }

}
document.addEventListener( 'DOMContentLoaded', () => popup() );