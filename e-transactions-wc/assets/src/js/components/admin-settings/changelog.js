const changeLog = () => {

    const popupContainer    = document.querySelector( '#JS-WCE-changelog-popup' );
    const btnOpenPopup      = document.querySelector( '#JS-WCE-changelog-open' );
    const btnClosePopup     = document.querySelector( '#JS-WCE-changelog-close' );
    const overlay           = document.querySelector( '#JS-WCE-changelog-overlay' );

    function openPopup() {
        popupContainer.classList.add( 'show' );
    }

    function closePopup() {
        popupContainer.classList.remove( 'show' );
    }

    if ( btnOpenPopup ) {
        btnOpenPopup.addEventListener( 'click', () => {
            openPopup();
        });
    }

    if ( btnClosePopup ) {
        btnClosePopup.addEventListener( 'click', () => {
            closePopup();
        });
    }

    if ( overlay ) {
        overlay.addEventListener( 'click', () => {
            closePopup();
        });
    }

}
document.addEventListener( 'DOMContentLoaded', () => changeLog() );