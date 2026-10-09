// Adds "copy" affordances to the Ignition error page so the exception can be
// quickly searched for or handed to an AI agent: a button to copy the full
// stack trace, a button to copy just the message, and a Cmd-C / Ctrl-C shortcut
// for the full stack trace. Built with the DOM API (no injected <style> and no
// inline handlers) to stay compatible with the page's script-nonce CSP.
(function () {
    'use strict';

    const getReport = () => window.data?.report ?? null;

    // The plain exception message - what you would typically search for.
    const buildMessage = (report) => (report.message ?? '').trim();

    // A PHP-style stack trace string, matching the shape of
    // Throwable::getTraceAsString() so it reads naturally to humans and AI.
    const buildStackTrace = (report) => {
        const frames = report.stacktrace ?? [];
        const [top] = frames;

        const header = `${report.exception_class ?? 'Exception'}: ${report.message ?? ''}`
            + (top?.file ? ` in ${top.file}:${top.line_number}` : '');

        const lines = frames.map((frame, index) => {
            if (frame.method === '[top]') {
                return `#${index} {main}`;
            }

            let call = '';
            if (frame.class) {
                call = `${frame.class}->${frame.method ?? ''}()`;
            } else if (frame.method) {
                call = `${frame.method}()`;
            }

            const location = `${frame.file ?? 'unknown'}(${frame.line_number ?? 0})`;

            return `#${index} ${location}${call ? `: ${call}` : ''}`;
        });

        return [header, 'Stack trace:', ...lines].join('\n');
    };

    const copyText = (text) => {
        if (navigator.clipboard?.writeText) {
            return navigator.clipboard.writeText(text);
        }

        // Fallback for non-secure contexts without the async clipboard API.
        return new Promise((resolve, reject) => {
            try {
                const textarea = document.createElement('textarea');
                textarea.value = text;
                textarea.style.cssText = 'position:fixed;top:-9999px;left:-9999px;opacity:0;';
                document.body.appendChild(textarea);
                textarea.focus();
                textarea.select();
                document.execCommand('copy');
                document.body.removeChild(textarea);
                resolve();
            } catch (error) {
                reject(error);
            }
        });
    };

    let toastTimer = null;

    const showToast = (message) => {
        let toast = document.getElementById('swissup-ignition-toast');

        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'swissup-ignition-toast';
            toast.style.cssText = `
                position: fixed;
                bottom: 5rem;
                right: 1.5rem;
                z-index: 2147483647;
                padding: 0.5rem 0.875rem;
                border-radius: 0.5rem;
                background: rgba(17, 24, 39, 0.95);
                color: #f9fafb;
                font: 500 13px/1.4 ui-sans-serif, system-ui, sans-serif;
                box-shadow: 0 10px 25px rgba(0, 0, 0, 0.35);
                pointer-events: none;
                opacity: 0;
                transform: translateY(0.5rem);
                transition: opacity .15s ease, transform .15s ease;
            `;
            document.body.appendChild(toast);
        }

        toast.textContent = message;
        void toast.offsetWidth; // Force a reflow so repeats re-run the transition.
        toast.style.opacity = '1';
        toast.style.transform = 'translateY(0)';

        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(0.5rem)';
        }, 1600);
    };

    const copyAndReport = (text, successMessage) => {
        if (!text) {
            return;
        }

        copyText(text)
            .then(() => showToast(successMessage))
            .catch(() => showToast('Could not copy to clipboard'));
    };

    // Heroicons (MIT) outline paths.
    const ICON_STACK = '<path stroke-linecap="round" stroke-linejoin="round" d="M6.429 9.75 2.25 12l4.179 2.25m0-4.5 5.571 3 5.571-3m-11.142 0L2.25 7.5 12 2.25l9.75 5.25-4.179 2.25m0 0L21.75 12l-4.179 2.25m0 0 4.179 2.25L12 21.75 2.25 16.5l4.179-2.25m11.142 0-5.571 3-5.571-3"></path>';
    const ICON_MESSAGE = '<path stroke-linecap="round" stroke-linejoin="round" d="M8.625 9.75a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z"></path>';

    const makeIcon = (svgPaths) => {
        const span = document.createElement('span');
        span.style.cssText = 'display:inline-flex;width:1rem;height:1rem;flex:none;';
        span.innerHTML = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"`
            + ` stroke-width="1.6" width="16" height="16" aria-hidden="true">${svgPaths}</svg>`;

        return span;
    };

    const makeButton = (label, svgPaths, onClick) => {
        const button = document.createElement('button');
        button.type = 'button';
        button.setAttribute('aria-label', label);
        button.title = label;
        button.style.cssText = `
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 0.875rem;
            border: 0;
            border-radius: 0.5rem;
            background: rgba(17, 24, 39, 0.92);
            color: #f9fafb;
            cursor: pointer;
            font: 500 13px/1 ui-sans-serif, system-ui, sans-serif;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
            transition: background .12s ease;
        `;

        button.appendChild(makeIcon(svgPaths));

        const text = document.createElement('span');
        text.textContent = label;
        button.appendChild(text);

        button.addEventListener('mouseenter', () => {
            button.style.background = 'rgba(55, 65, 81, 0.98)';
        });
        button.addEventListener('mouseleave', () => {
            button.style.background = 'rgba(17, 24, 39, 0.92)';
        });
        button.addEventListener('click', onClick);

        return button;
    };

    const buildToolbar = (stackTrace, message) => {
        const bar = document.createElement('div');
        bar.id = 'swissup-ignition-copy-bar';
        bar.style.cssText = `
            position: fixed;
            bottom: 1.5rem;
            right: 1.5rem;
            z-index: 2147483646;
            display: flex;
            gap: 0.5rem;
        `;

        bar.appendChild(makeButton('Copy stack trace', ICON_STACK, () => {
            copyAndReport(stackTrace, 'Stack trace copied');
        }));
        bar.appendChild(makeButton('Copy message', ICON_MESSAGE, () => {
            copyAndReport(message, 'Message copied');
        }));

        document.body.appendChild(bar);
    };

    // Make the exception text trivially readable by an automation agent driving
    // the page (Playwright etc.) without touching the clipboard: a window API
    // and hidden, stable DOM nodes that page.locator(...).textContent() can read.
    const exposeForAutomation = (report, stackTrace, message) => {
        const mirror = (id, text) => {
            const pre = document.createElement('pre');
            pre.id = id;
            pre.hidden = true;
            pre.textContent = text;
            document.body.appendChild(pre);
        };

        mirror('swissup-ignition-stacktrace', stackTrace);
        mirror('swissup-ignition-message', message);

        window.swissupIgnition = {
            report,
            message,
            stackTrace,
            copyStackTrace: () => copyText(stackTrace),
            copyMessage: () => copyText(message)
        };
    };

    const hasTextSelection = () => (window.getSelection?.().toString() ?? '').trim() !== '';

    const isEditableTarget = (target) => {
        const tag = target?.tagName?.toLowerCase();

        return tag === 'input' || tag === 'textarea' || target?.isContentEditable === true;
    };

    const bindCopyShortcut = (stackTrace) => {
        document.addEventListener('keydown', (event) => {
            const isCopyKey = (event.key === 'c' || event.key === 'C')
                && (event.metaKey || event.ctrlKey)
                && !event.shiftKey && !event.altKey;

            if (!isCopyKey) {
                return;
            }

            // Respect an explicit selection or a focused field - the user is
            // copying something specific, not the whole error.
            if (hasTextSelection() || isEditableTarget(event.target)) {
                return;
            }

            event.preventDefault();
            copyAndReport(stackTrace, 'Stack trace copied');
        });
    };

    const init = () => {
        const report = getReport();

        if (!report || document.getElementById('swissup-ignition-copy-bar')) {
            return;
        }

        const stackTrace = buildStackTrace(report);
        const message = buildMessage(report);

        buildToolbar(stackTrace, message);
        bindCopyShortcut(stackTrace);
        exposeForAutomation(report, stackTrace, message);
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
