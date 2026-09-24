"use strict";

// Only what the WooCommerce order edit screen needs.
import './order.less';
import { initReviewPanel } from '../shared/review';

document.addEventListener('DOMContentLoaded', () => {
    initReviewPanel();
});
