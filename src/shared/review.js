"use strict";

/**
 * Behaviour shared by the review screen and the order edit meta box.
 *
 * Both show the same decision panel, so the reason guard is written once here
 * and imported by whichever bundle is on the page.
 */

/**
 * Show the rejection reason box only when a rejection is actually being made,
 * and stop the save when the store requires a reason and none was given.
 */
export const initReviewPanel = () => {
    const select = document.querySelector('#ddwcmpa-status-select');

    if (!select) {
        return;
    }

    const reasonLabel = document.querySelector('.ddwcmpa-reason-label');
    const reasonField = document.querySelector('#ddwcmpa-rejection-reason');

    const sync = () => {
        if (reasonLabel) {
            reasonLabel.classList.toggle('ddwcmpa-hide', -1 === ['rejected', 'info_required'].indexOf(select.value));
        }
    };

    select.addEventListener('change', sync);
    sync();

    const form = select.closest('form');

    if (form) {
        form.addEventListener('submit', e => {
            if (-1 !== ['rejected', 'info_required'].indexOf(select.value) && reasonField && !reasonField.value.trim()) {
                e.preventDefault();
                window.alert(ddwcmpaAdminObj.i18n.reasonRequired);
                reasonField.focus();
            }
        });
    }
};

