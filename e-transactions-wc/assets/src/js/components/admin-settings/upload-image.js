const uploadImage = () => {

    const uploadBtns    = document.querySelectorAll( '.wce-upload-image .wce-upload' );
    const resetBtns     = document.querySelectorAll( '.wce-upload-image .wce-reset' );
    
    uploadBtns.forEach( btn => {
        btn.addEventListener( 'click', e => {
            e.preventDefault();

            const parent        = btn.closest('.wce-upload-image');
            const previewImage  = parent.querySelector('.wce-preview');
            const inputHidden   = parent.querySelector('.wce-input');
            const resetBtn      = parent.querySelector('.wce-reset');

            const customUploader = wp.media({
                library : {
                    type : 'image'
                },
                multiple: false
            }).on( 'select', function() {
                const attachment    = customUploader.state().get( 'selection' ).first().toJSON();
                previewImage.src    = attachment.url;
                inputHidden.value   = attachment.url;
                resetBtn.classList.add('show');
            });
    
            customUploader.open();
        });
    });

    resetBtns.forEach( btn => {
        btn.addEventListener( 'click', e => {
            e.preventDefault();

            const parent        = btn.closest('.wce-upload-image');
            const previewImage  = parent.querySelector('.wce-preview');
            const inputHidden   = parent.querySelector('.wce-input');

            previewImage.src    = previewImage.dataset.default;
            inputHidden.value   = previewImage.dataset.default;
            btn.classList.remove('show');
        });
    });

};
document.addEventListener( 'DOMContentLoaded', () => uploadImage() );