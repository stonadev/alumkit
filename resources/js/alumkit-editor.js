import EditorJS from '@editorjs/editorjs';
import Header from '@editorjs/header';
import List from '@editorjs/list';
import Paragraph from '@editorjs/paragraph';
import Table from '@editorjs/table';
import ImageTool from '@editorjs/image';

import '../css/alumkit-editor.css';

/**
 * Editor.js WYSIWYG field bootstrap.
 *
 * The core and every tool in this editor.js version inject their own styles
 * at runtime, so the only CSS built here is the component shell.
 */

function safeParse(s) {
  try {
    return JSON.parse(s);
  } catch {
    return undefined; // legacy plain-text bodies start an empty editor
  }
}

function xsrfToken() {
  const match = document.cookie.match(/(?:^|; )XSRF-TOKEN=([^;]*)/);
  return match ? decodeURIComponent(match[1]) : '';
}

function initEditor(el) {
  const input = el.querySelector('input[type="hidden"]');
  const holder = el.querySelector('.alumkit-editor-holder');
  const data = el.dataset.value ? safeParse(el.dataset.value) : undefined;

  const editor = new EditorJS({
    holder,
    data,
    tools: {
      paragraph: Paragraph,
      header: { class: Header, inlineToolbar: true },
      list: { class: List, inlineToolbar: true },
      table: { class: Table, inlineToolbar: true },
      image: {
        class: ImageTool,
        config: {
          uploader: {
            uploadByFile,
            uploadByUrl,
          },
        },
      },
    },
  });

  async function uploadByFile(file) {
    const fd = new FormData();
    fd.append('file', file);
    const res = await fetch(el.dataset.uploadUrl, {
      method: 'POST',
      headers: { Accept: 'application/json', 'X-XSRF-TOKEN': xsrfToken() },
      body: fd,
    });
    const json = await res.json();
    if (!res.ok || json.success !== 1) throw new Error('Image upload failed');
    return json;
  }

  function uploadByUrl(url) {
    return { success: 1, file: { url } };
  }

  const form = el.closest('form');
  if (form) {
    form.addEventListener('submit', async (e) => {
      if (form.__alumkitEditorSubmitting) return; // re-entered via requestSubmit: let it through
      e.preventDefault();
      form.__alumkitEditorSubmitting = true;
      try {
        const data = await editor.save();
        input.value = data.blocks.length ? JSON.stringify(data) : '';
        // submit only after every editor in the form has saved (N-editor safe)
        form.__alumkitEditorSaved = (form.__alumkitEditorSaved ?? 0) + 1;
        if (form.__alumkitEditorSaved === form.querySelectorAll('[data-alumkit-editor]').length) {
          form.requestSubmit();
        }
      } catch {
        form.__alumkitEditorSubmitting = false;
        form.__alumkitEditorSaved = 0;
        alert('Could not save the editor content. Please check the highlighted blocks.');
      }
    }, true);
  }
}

function initAll() {
  document.querySelectorAll('[data-alumkit-editor]').forEach(initEditor);
}

if (!window.__alumkitEditorInit) {
  window.__alumkitEditorInit = true;
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAll);
  } else {
    initAll();
  }
}
