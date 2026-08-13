import { useEffect, useRef } from 'react';
import {
    Bold,
    Italic,
    Underline,
    Link2,
    List,
    ListOrdered,
    Heading1,
    Heading2,
    Heading3,
    RemoveFormatting,
    Undo2,
    Redo2,
} from 'lucide-react';

function ToolButton({ title, onClick, children }) {
    return (
        <button
            type="button"
            title={title}
            onMouseDown={(e) => e.preventDefault()}
            onClick={onClick}
            className="inline-flex items-center justify-center rounded-md border border-neutral-200 bg-white px-2.5 py-2 text-neutral-600 hover:bg-neutral-50 hover:text-brand-600 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300 dark:hover:bg-neutral-800"
        >
            {children}
        </button>
    );
}

function runCommand(command, value = null) {
    try {
        document.execCommand('styleWithCSS', false, false);
    } catch {
        // ignored
    }
    document.execCommand(command, false, value);
}

export default function RichTextEditor({ value = '', onChange, placeholder = 'Write content here...' }) {
    const editorRef = useRef(null);

    useEffect(() => {
        const el = editorRef.current;
        if (!el) return;
        if (document.activeElement === el) return;
        if (el.innerHTML !== (value ?? '')) {
            el.innerHTML = value ?? '';
        }
    }, [value]);

    const emit = () => {
        onChange?.(editorRef.current?.innerHTML ?? '');
    };

    const apply = (command, commandValue = null) => {
        editorRef.current?.focus();
        runCommand(command, commandValue);
        emit();
    };

    const applyHeading = (level) => {
        editorRef.current?.focus();
        runCommand('formatBlock', `H${level}`);
        emit();
    };

    const applyLink = () => {
        editorRef.current?.focus();
        const url = window.prompt('Enter link URL');
        if (!url) return;
        const finalUrl = /^(https?:|mailto:|tel:|#|\{\{)/i.test(url) ? url : `https://${url}`;
        runCommand('createLink', finalUrl);
        emit();
    };

    const handlePaste = (event) => {
        event.preventDefault();
        const text = (event.clipboardData || window.clipboardData).getData('text/plain');
        document.execCommand('insertText', false, text);
        emit();
    };

    return (
        <div className="space-y-3">
            <div className="flex flex-wrap gap-2">
                <ToolButton title="Bold" onClick={() => apply('bold')}>
                    <Bold className="h-4 w-4" />
                </ToolButton>
                <ToolButton title="Italic" onClick={() => apply('italic')}>
                    <Italic className="h-4 w-4" />
                </ToolButton>
                <ToolButton title="Underline" onClick={() => apply('underline')}>
                    <Underline className="h-4 w-4" />
                </ToolButton>
                <ToolButton title="Heading 1" onClick={() => applyHeading(1)}>
                    <Heading1 className="h-4 w-4" />
                </ToolButton>
                <ToolButton title="Heading 2" onClick={() => applyHeading(2)}>
                    <Heading2 className="h-4 w-4" />
                </ToolButton>
                <ToolButton title="Heading 3" onClick={() => applyHeading(3)}>
                    <Heading3 className="h-4 w-4" />
                </ToolButton>
                <ToolButton title="Bullet list" onClick={() => apply('insertUnorderedList')}>
                    <List className="h-4 w-4" />
                </ToolButton>
                <ToolButton title="Numbered list" onClick={() => apply('insertOrderedList')}>
                    <ListOrdered className="h-4 w-4" />
                </ToolButton>
                <ToolButton title="Insert link" onClick={applyLink}>
                    <Link2 className="h-4 w-4" />
                </ToolButton>
                <ToolButton title="Undo" onClick={() => apply('undo')}>
                    <Undo2 className="h-4 w-4" />
                </ToolButton>
                <ToolButton title="Redo" onClick={() => apply('redo')}>
                    <Redo2 className="h-4 w-4" />
                </ToolButton>
                <ToolButton title="Clear formatting" onClick={() => apply('removeFormat')}>
                    <RemoveFormatting className="h-4 w-4" />
                </ToolButton>
            </div>

            <div
                ref={editorRef}
                contentEditable
                suppressContentEditableWarning
                onInput={emit}
                onBlur={emit}
                onPaste={handlePaste}
                data-placeholder={placeholder}
                className="min-h-[320px] w-full rounded-xl border border-neutral-300 bg-white px-4 py-3 text-sm text-neutral-900 shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-100 [&:empty:before]:content-[attr(data-placeholder)] [&:empty:before]:text-neutral-400"
                style={{ whiteSpace: 'normal' }}
            />
        </div>
    );
}
