"use strict";

import './front.less';

// Custom tooltip functions
function showTooltip(e) {
    const tip = e.target.getAttribute('data-tip');
    if (tip) {
        hideTooltip(); // Remove any existing tooltip

        const tooltip = document.createElement('div');
        tooltip.className = 'ddwcmpa-custom-tooltip';
        tooltip.textContent = tip;
        document.body.appendChild(tooltip);

        const rect = e.target.getBoundingClientRect();
        const tooltipRect = tooltip.getBoundingClientRect();

        let left = rect.left + rect.width / 2 - tooltipRect.width / 2;
        let top = rect.top - tooltipRect.height - 8;

        // Adjust if tooltip goes off screen
        if (left < 8) {
            left = 8;
        } else if (left + tooltipRect.width > window.innerWidth - 8) {
            left = window.innerWidth - tooltipRect.width - 8;
        }

        if (top < 8) {
            top = rect.bottom + 8;
            tooltip.classList.add('ddwcmpa-tooltip-bottom');
        }

        tooltip.style.left = left + 'px';
        tooltip.style.top = top + 'px';
        tooltip.style.opacity = '1';
    }
}

function hideTooltip() {
    const existingTooltip = document.querySelector('.ddwcmpa-custom-tooltip');
    if (existingTooltip) {
        existingTooltip.remove();
    }
}

const ajaxPost = (formData) => {
    formData.append('nonce', ddwcmpaFrontObj.ajax.ajaxNonce);
    formData.append('action', 'ddwcmpa_handle_prescription_session');

    return fetch(ddwcmpaFrontObj.ajax.ajaxUrl, { method: 'post', body: formData }).then(response => response.json());
};

const refreshCheckout = () => {
    document.querySelector('body').dispatchEvent(new Event('update_checkout'));
};

const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'heic', 'pdf', 'doc', 'docx', 'txt'];

const hasValidExtension = (file) => {
    const parts = file.name.split('.');
    return ALLOWED_EXTENSIONS.indexOf(parts[parts.length - 1].toLowerCase()) !== -1;
};

// Count the tiles already rendered so the max-files limit is enforced before
// the browser starts uploading anything.
const countAttachments = (container) => {
    return container.querySelectorAll('label[data-attachment-id]').length;
};

const showMessage = (container, message, isError) => {
    let notice = container.querySelector('.ddwcmpa-inline-notice');

    if (!notice) {
        notice = document.createElement('div');
        notice.className = 'ddwcmpa-inline-notice';
        container.prepend(notice);
    }

    notice.textContent = message;
    notice.classList.toggle('is-error', !!isError);
    notice.classList.remove('ddwcmpa-hide');

    window.clearTimeout(notice.dataset.timer);
    notice.dataset.timer = window.setTimeout(() => notice.classList.add('ddwcmpa-hide'), 6000);
};

// fetch() cannot report upload progress, so the one request the customer waits
// on goes through XHR instead.
const uploadWithProgress = (formData, onProgress) => {
    formData.append('nonce', ddwcmpaFrontObj.ajax.ajaxNonce);
    formData.append('action', 'ddwcmpa_handle_prescription_session');

    return new Promise((resolve, reject) => {
        const xhr = new XMLHttpRequest();

        xhr.open('POST', ddwcmpaFrontObj.ajax.ajaxUrl, true);

        xhr.upload.addEventListener('progress', e => {
            if (e.lengthComputable) {
                onProgress(Math.round((e.loaded / e.total) * 100));
            }
        });

        xhr.addEventListener('load', () => {
            try {
                resolve(JSON.parse(xhr.responseText));
            } catch (err) {
                reject(err);
            }
        });

        xhr.addEventListener('error', reject);
        xhr.send(formData);
    });
};

const initLightbox = () => {
    const overlay = document.querySelector('.ddwcmpa-lightbox-overlay');

    if (!overlay || overlay.dataset.initialized) {
        return;
    }

    overlay.dataset.initialized = 'true';

    const close = () => overlay.classList.add('ddwcmpa-hide');

    overlay.addEventListener('click', e => {
        if (e.target === overlay || e.target.classList.contains('ddwcmpa-lightbox-close')) {
            close();
        }
    });

    document.addEventListener('keyup', e => {
        if ('Escape' === e.key) {
            close();
        }
    });

    document.addEventListener('click', e => {
        const link = e.target.closest('a.ddwcmpa-lightbox');

        if (!link) {
            return;
        }

        e.preventDefault();
        overlay.querySelector('img').src = link.getAttribute('href');
        overlay.classList.remove('ddwcmpa-hide');
    });
};

// The My Account screen can show one of these per order, so every entry point
// wires up each container it finds rather than assuming there is only one.
const initPrescriptionAttachment = () => {
    document.querySelectorAll('.ddwcmpa-prescription-attachment-container').forEach(initPrescriptionContainer);
};

const initPrescriptionContainer = (prescriptionContainerElement) => {
    const loaderElement = document.querySelector('.ddwcmpa-loader-wrap');

    if (prescriptionContainerElement && !prescriptionContainerElement.dataset.initialized) {
        prescriptionContainerElement.dataset.initialized = 'true';

        prescriptionContainerElement.addEventListener('click', e => {
            // closest, not the target itself: every control in here now carries an
            // inline SVG, and a click lands on the svg rather than on the button.
            const tab = e.target.closest('.ddwcmpa-tab');
            const removeButton = e.target.closest('.ddwcmpa-remove-prescription');
            const approvalButton = e.target.closest('.ddwcmpa-approval-submit');

            if (tab) {
                const attachment = tab.getAttribute('data-attachment');

                const formData = new FormData();

                formData.append('type', 'when');
                formData.append('attachment_when', attachment);
                formData.append('nonce', ddwcmpaFrontObj.ajax.ajaxNonce);
                formData.append('action', 'ddwcmpa_handle_prescription_session');

                loaderElement.classList.remove('ddwcmpa-hide');

                fetch(ddwcmpaFrontObj.ajax.ajaxUrl, {
                    method: "post",
                    body: formData
                })
                    .then(response => response.json())
                    .then(response => {
                        loaderElement.classList.add('ddwcmpa-hide');
                        tab.classList.add('ddwcmpa-active');

                        const isNow = 'now' === attachment;
                        const container = tab.closest('.ddwcmpa-prescription-attachment-container');

                        container.querySelector('.ddwcmpa-upload-form').classList.toggle('ddwcmpa-hide', !isNow);
                        container.querySelector('.ddwcmpa-attachment-later-description').classList.toggle('ddwcmpa-hide', isNow);
                        container.querySelector('li[data-attachment="' + (isNow ? 'later' : 'now') + '"]').classList.remove('ddwcmpa-active');

                        document.querySelector('body').dispatchEvent(new Event('update_checkout'));
                    })
                    .catch(() => {
                    });
            } else if (removeButton) {
                const formData = new FormData();
                const labelElement = removeButton.closest('label');
                const orderIdElement = prescriptionContainerElement.querySelector('input[name="ddwcmpa_order_id"]');

                formData.append('type', 'remove');
                formData.append('attachment_id', labelElement.getAttribute('data-attachment-id'));
                formData.append('ddwcmpa_order_id', orderIdElement ? orderIdElement.value : '');
                formData.append('nonce', ddwcmpaFrontObj.ajax.ajaxNonce);
                formData.append('action', 'ddwcmpa_handle_prescription_session');

                loaderElement.classList.remove('ddwcmpa-hide');

                fetch(ddwcmpaFrontObj.ajax.ajaxUrl, {
                    method: "post",
                    body: formData
                })
                    .then(response => response.json())
                    .then(response => {
                        loaderElement.classList.add('ddwcmpa-hide');

                        if (response.success) {
                            labelElement.remove();
                        }

                        document.querySelector('body').dispatchEvent(new Event('update_checkout'));
                    })
                    .catch(() => {
                    });
            } else if (approvalButton) {
                const formData = new FormData();
                const orderIdElement = prescriptionContainerElement.querySelector('input[name="ddwcmpa_order_id"]');

                formData.append('type', 'send_for_approval');
                formData.append('ddwcmpa_order_id', orderIdElement ? orderIdElement.value : '');
                formData.append('nonce', ddwcmpaFrontObj.ajax.ajaxNonce);
                formData.append('action', 'ddwcmpa_handle_prescription_session');

                loaderElement.classList.remove('ddwcmpa-hide');

                fetch(ddwcmpaFrontObj.ajax.ajaxUrl, {
                    method: "post",
                    body: formData
                })
                    .then(response => response.json())
                    .then(response => {
                        loaderElement.classList.add('ddwcmpa-hide');
                        if (response.success) {
                            const tempDiv = document.createElement('div');
                            tempDiv.innerHTML = response.data;
                            const replacement = tempDiv.firstElementChild;

                            prescriptionContainerElement.replaceWith(replacement);
                            tempDiv.remove();
                            // Re-initialize the replacement only. Re-scanning the page
                            // would be harmless but pointless, and on My Account it would
                            // walk every other card as well.
                            initPrescriptionContainer(replacement);
                        } else {
                            alert(response.message);
                        }
                    })
                    .catch(() => {
                    });
            }
        });

        // Initialize custom tooltips
        const helpTips = prescriptionContainerElement.querySelectorAll('.ddwcmpa-help-tip');
        helpTips.forEach(tip => {
            tip.addEventListener('mouseenter', showTooltip);
            tip.addEventListener('mouseleave', hideTooltip);
        });

        const prescriptionForm = prescriptionContainerElement.querySelector('form.ddwcmpa-upload-form');

        if (prescriptionForm) {
            const dropzone = prescriptionForm.querySelector('.ddwcmpa-dropzone');
            const fileInput = prescriptionForm.querySelector('#ddwcmpa-prescription-attachment');

            const submitFiles = (files) => {
                const maxFiles = parseInt(ddwcmpaFrontObj.config.maxFiles, 10) || 0;

                if (!files || !files.length) {
                    return;
                }

                for (let i = 0; i < files.length; i++) {
                    if (!hasValidExtension(files[i])) {
                        showMessage(prescriptionContainerElement, ddwcmpaFrontObj.i18n.attachmentExtensionError, true);
                        return;
                    }
                }

                if (maxFiles && (countAttachments(prescriptionForm) + files.length) > maxFiles) {
                    showMessage(prescriptionContainerElement, ddwcmpaFrontObj.i18n.maxFilesError, true);
                    return;
                }

                const formData = new FormData();
                const orderIdElement = prescriptionContainerElement.querySelector('input[name="ddwcmpa_order_id"]');

                for (let i = 0; i < files.length; i++) {
                    formData.append('ddwcmpa_prescription_attachment[]', files[i]);
                }

                formData.append('type', 'upload');
                formData.append('ddwcmpa_order_id', orderIdElement ? orderIdElement.value : '');

                if (dropzone) {
                    dropzone.classList.add('is-uploading');
                    dropzone.style.setProperty('--ddwcmpa-progress', '0%');
                }

                loaderElement.classList.remove('ddwcmpa-hide');

                uploadWithProgress(formData, percent => {
                    if (dropzone) {
                        dropzone.style.setProperty('--ddwcmpa-progress', percent + '%');
                    }
                })
                    .then(response => {
                        loaderElement.classList.add('ddwcmpa-hide');

                        if (dropzone) {
                            dropzone.classList.remove('is-uploading');
                        }

                        if (fileInput) {
                            fileInput.value = '';
                        }

                        const tileBox = prescriptionForm.querySelector('.ddwcmpa-prescription-attachment-box');

                        if (response.success && tileBox && response.attachments_html && response.attachments_html.length) {
                            response.attachments_html.forEach(html => tileBox.insertAdjacentHTML('beforeend', html));
                        }

                        if (response.message) {
                            showMessage(prescriptionContainerElement, response.message, !response.success);
                        }

                        refreshCheckout();
                    })
                    .catch(() => {
                        loaderElement.classList.add('ddwcmpa-hide');

                        if (dropzone) {
                            dropzone.classList.remove('is-uploading');
                        }
                    });
            };

            prescriptionForm.addEventListener('submit', e => {
                e.preventDefault();
                submitFiles(fileInput ? fileInput.files : []);
            });

            if (fileInput) {
                fileInput.addEventListener('change', () => submitFiles(fileInput.files));
            }

            if (dropzone) {
                ['dragenter', 'dragover'].forEach(type => {
                    dropzone.addEventListener(type, e => {
                        e.preventDefault();
                        e.stopPropagation();
                        dropzone.classList.add('is-dragging');
                    });
                });

                ['dragleave', 'drop'].forEach(type => {
                    dropzone.addEventListener(type, e => {
                        e.preventDefault();
                        e.stopPropagation();
                        dropzone.classList.remove('is-dragging');
                    });
                });

                dropzone.addEventListener('drop', e => {
                    if (e.dataTransfer && e.dataTransfer.files.length) {
                        submitFiles(e.dataTransfer.files);
                    }
                });

                // The dropzone is a label, so the keyboard already reaches the input;
                // this only makes Enter and Space behave like the click does.
                dropzone.addEventListener('keydown', e => {
                    if (('Enter' === e.key || ' ' === e.key) && fileInput) {
                        e.preventDefault();
                        fileInput.click();
                    }
                });
            }
        }

    }
};

const handleBlocksInjection = () => {
    if (!document.body.classList.contains('woocommerce-cart') && !document.body.classList.contains('woocommerce-checkout')) {
        return;
    }

    const isCartBlock = document.querySelector('.wc-block-cart');
    const isCheckoutBlock = document.querySelector('.wc-block-checkout');

    if (isCartBlock || isCheckoutBlock) {
        if (!document.querySelector('.ddwcmpa-prescription-attachment-container')) {
            const config = ddwcmpaFrontObj.config;
            if (!config) return;

            const position = isCartBlock ? config.cart_page_position : config.checkout_page_position;

            let targetSelector = isCartBlock ? '.wc-block-cart__main' : '.wc-block-checkout__main';
            let injectionMethod = 'prepend';

            if (isCartBlock) {
                switch (position) {
                    case 'before_cart_table':
                        targetSelector = '.wc-block-cart__main';
                        injectionMethod = 'prepend';
                        break;
                    case 'after_cart_table':
                        targetSelector = '.wc-block-cart__main';
                        injectionMethod = 'append';
                        break;
                    case '10':
                    case '20':
                        targetSelector = '.wc-block-cart__submit-container';
                        injectionMethod = 'prepend';
                        break;
                }
            } else {
                switch (position) {
                    case 'before_checkout_form':
                        targetSelector = '.wc-block-checkout__main';
                        injectionMethod = 'prepend';
                        break;
                    case 'after_checkout_form':
                        targetSelector = '.wc-block-checkout__main';
                        injectionMethod = 'append';
                        break;
                }
            }

            const targetElement = document.querySelector(targetSelector);
            if (targetElement) {
                const formData = new FormData();
                formData.append('action', 'ddwcmpa_get_prescription_ui');
                formData.append('nonce', ddwcmpaFrontObj.ajax.ajaxNonce);

                fetch(ddwcmpaFrontObj.ajax.ajaxUrl, {
                    method: "post",
                    body: formData
                })
                    .then(response => response.json())
                    .then(response => {
                        if (response.success && response.data) {
                            // Re-verify target and check if already injected
                            const currentTarget = document.querySelector(targetSelector);
                            if (currentTarget && !document.querySelector('.ddwcmpa-prescription-attachment-container')) {
                                const tempDiv = document.createElement('div');
                                tempDiv.innerHTML = response.data;
                                const content = tempDiv.firstElementChild;

                                if (injectionMethod === 'prepend') {
                                    currentTarget.prepend(content);
                                } else {
                                    currentTarget.append(content);
                                }

                                tempDiv.remove();
                                initPrescriptionAttachment();
                            }
                        }
                    })
                    .catch(() => {});
            }
        }
    }
};


document.addEventListener('DOMContentLoaded', () => {
    initPrescriptionAttachment();
    initLightbox();

    // Handle Blocks injection
    setTimeout(handleBlocksInjection, 1000); // Wait for blocks to load

    // Periodically check for blocks because they might re-render
    setInterval(handleBlocksInjection, 3000);
});