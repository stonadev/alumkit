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
    // Keep the hidden input in sync before any submit. Editor.js needs a beat to
    // persist the block the user just left; awaiting save() inside the submit
    // handler made the first click after editing drop the submit (the save raced
    // the blur triggered by the click itself), so the page only saved on the
    // second click. Sync on focusout instead: clicking Save/Cancel blurs the
    // editor first, and the submit handler then just flushes any pending save.
    let saving = false;
    async function sync() {
      if (saving) return;
      saving = true;
      try {
        const data = await editor.save();
        input.value = data.blocks.length ? JSON.stringify(data) : '';
      } finally {
        saving = false;
      }
    }
    el.__alumkitSync = sync;
    form.addEventListener('focusout', (e) => {
      // Save when focus leaves this editor (clicking Save/Cancel blurs it first).
      if (el.contains(e.target) && !el.contains(e.relatedTarget)) sync();
    }, true);
    form.addEventListener('submit', async (e) => {
      // Re-entered after we flushed the editors: submit for real.
      if (el.__alumkitSubmitted) return;
      e.preventDefault();
      el.__alumkitSubmitted = true;
      try {
        // Every editor on the form syncs (N-editor safe), then submit once.
        await Promise.all(
          [...form.querySelectorAll('[data-alumkit-editor]')].map((editorEl) =>
            editorEl.__alumkitSync?.()
          )
        );
        // form.submit() bypasses this handler (no re-entrancy, no race) and still
        // sends the fresh hidden inputs with the normal form encoding.
        form.submit();
      } catch {
        el.__alumkitSubmitted = false;
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
