import DOMPurify from 'dompurify';

const ALLOWED_TAGS = [
  'p',
  'br',
  'strong',
  'b',
  'em',
  'i',
  'u',
  's',
  'strike',
  'mark',
  'ul',
  'ol',
  'li',
  'blockquote',
  'pre',
  'code',
  'h1',
  'h2',
  'h3',
  'h4',
  'h5',
  'h6',
  'hr',
  'a',
  'span',
];

const ALLOWED_ATTR = ['href', 'target', 'rel', 'class', 'data-type', 'data-id', 'data-label'];

const MENTION_ATTRIBUTES = ['data-type', 'data-id', 'data-label'];

const ALLOWED_URI = /^(?:(?:https?|mailto):|[^a-z]|[a-z+.-]+(?:[^a-z+.\-:]|$))/i;

if (typeof DOMPurify.addHook === 'function') {
  DOMPurify.addHook('afterSanitizeAttributes', node => {
    if (node.tagName === 'SPAN') {
      if (node.getAttribute('class') !== 'mention') {
        node.removeAttribute('class');
      }
    } else {
      node.removeAttribute('class');
      MENTION_ATTRIBUTES.forEach(attribute => node.removeAttribute(attribute));
    }

    if (node.tagName !== 'A') {
      return;
    }

    if (node.getAttribute('target') !== '_blank') {
      node.removeAttribute('target');
      node.removeAttribute('rel');

      return;
    }

    node.setAttribute('rel', 'noopener noreferrer');
  });
}

export function sanitizeHtml(html) {
  if (!html || typeof DOMPurify.sanitize !== 'function') {
    return '';
  }

  return DOMPurify.sanitize(html, {
    ALLOWED_TAGS,
    ALLOWED_ATTR,
    ALLOW_DATA_ATTR: false,
    ALLOWED_URI_REGEXP: ALLOWED_URI,
  });
}
