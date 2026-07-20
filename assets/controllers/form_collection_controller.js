import { Controller } from '@hotwired/stimulus';

/*
 * Adds/removes entries in a Symfony CollectionType (allow_add / allow_delete).
 * Usage:
 *   <div data-controller="form-collection"
 *        data-form-collection-index-value="{{ form.field|length }}"
 *        data-form-collection-prototype-value="{{ form_widget(form.field.vars.prototype.child)|e('html_attr') }}">
 *     <div data-form-collection-target="entries">...existing rows...</div>
 *     <button data-action="form-collection#add">Add</button>
 *   </div>
 */
export default class extends Controller {
    static targets = ['entries'];
    static values = {
        prototype: String,
        index: Number,
    };

    add(event) {
        event.preventDefault();
        const html = this.prototypeValue.replace(/__name__/g, this.indexValue);
        this.indexValue++;
        this.entriesTarget.insertAdjacentHTML('beforeend', this.wrap(html));
    }

    remove(event) {
        event.preventDefault();
        const entry = event.target.closest('[data-form-collection-target="entry"]');
        if (entry) {
            entry.remove();
        }
    }

    wrap(inner) {
        return `<div class="flex items-center gap-2" data-form-collection-target="entry">`
            + `<div class="flex-1">${inner}</div>`
            + `<button type="button" data-action="form-collection#remove" class="icon-btn flex-none text-slate-400 hover:bg-red-50 hover:text-red-600" aria-label="Remove">`
            + `<svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z"/></svg>`
            + `</button></div>`;
    }
}
