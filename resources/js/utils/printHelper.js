import html2pdf from 'html2pdf.js';
import Toastify from 'toastify-js';
import 'toastify-js/src/toastify.css';

export function isMobileDevice() {
  return (
    window.innerWidth <= 768 ||
    /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)
  );
}

export function printOrDownloadPdf(html, filename = 'document.pdf', options = {}) {
  // Always use Clean Iframe Print (even on mobile/desktop) to open native print dialog
  let iframe = document.getElementById('printFrame');
  if (!iframe) {
    iframe = document.createElement('iframe');
    iframe.id = 'printFrame';
    iframe.style.position = 'fixed';
    iframe.style.right = '0';
    iframe.style.bottom = '0';
    iframe.style.width = '0';
    iframe.style.height = '0';
    iframe.style.border = 'none';
    document.body.appendChild(iframe);
  }

  const doc = iframe.contentWindow.document;
  doc.open();
  doc.write(html);
  doc.close();

  setTimeout(() => {
    iframe.contentWindow.focus();
    iframe.contentWindow.print();
  }, 400);
}

// Global window registration
if (typeof window !== 'undefined') {
  window.printOrDownloadPdf = printOrDownloadPdf;
  window.isMobileDevice = isMobileDevice;
}
