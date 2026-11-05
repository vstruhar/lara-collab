import { useComputedColorScheme } from '@mantine/core';
import { RichTextEditor as Editor, Link } from '@mantine/tiptap';
import Highlight from '@tiptap/extension-highlight';
import Mention from '@tiptap/extension-mention';
import { Placeholder } from '@tiptap/extensions';
import { useEditor } from '@tiptap/react';
import StarterKit from '@tiptap/starter-kit';
import { forwardRef, useImperativeHandle, useRef } from 'react';
import suggestion from './RichTextEditor/Mention/suggestion.js';
import classes from './css/RichTextEditor.module.css';

const RichTextEditor = forwardRef(function RichTextEditor(
  {
    onChange,
    onFocus,
    onBlur,
    placeholder,
    content,
    height = 200,
    readOnly = false,
    fill = false,
    ...props
  },
  ref
) {
  const onFocusRef = useRef(onFocus);
  const onBlurRef = useRef(onBlur);
  onFocusRef.current = onFocus;
  onBlurRef.current = onBlur;

  const editor = useEditor({
    editable: !readOnly,
    shouldRerenderOnTransaction: true,
    onFocus: () => onFocusRef.current?.(),
    onBlur: () => onBlurRef.current?.(),
    extensions: [
      StarterKit.configure({ link: false }),
      Link,
      Highlight,
      Placeholder.configure({ placeholder }),
      Mention.configure({
        HTMLAttributes: {
          class: 'mention',
        },
        suggestion,
      }),
    ],
    content,
    onUpdate({ editor }) {
      onChange(editor.getHTML());
    },
  });

  useImperativeHandle(ref, () => ({
    setContent(content) {
      editor.commands.setContent(content, { emitUpdate: false });
    },
  }));

  const computedColorScheme = useComputedColorScheme();

  return (
    <Editor
      editor={editor}
      {...props}
      className={fill ? classes.fill : props.className}
    >
      <Editor.Toolbar
        sticky
        stickyOffset={60}
      >
        <Editor.ControlsGroup>
          <Editor.Bold />
          <Editor.Italic />
          <Editor.Underline />
          <Editor.Strikethrough />
          <Editor.Highlight />
        </Editor.ControlsGroup>

        <Editor.ControlsGroup>
          <Editor.BulletList />
          <Editor.OrderedList />
        </Editor.ControlsGroup>

        <Editor.ControlsGroup>
          <Editor.Link />
          <Editor.Unlink />
        </Editor.ControlsGroup>

        <Editor.ControlsGroup>
          <Editor.Code />
          <Editor.Blockquote />
        </Editor.ControlsGroup>
      </Editor.Toolbar>

      <Editor.Content
        bg={computedColorScheme === 'dark' ? 'dark.6' : 'white'}
        className={classes.content}
        style={{ '--rich-text-editor-height': `${height}px` }}
      />
    </Editor>
  );
});

export default RichTextEditor;
