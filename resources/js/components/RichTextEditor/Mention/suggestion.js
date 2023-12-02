import { ReactRenderer } from '@tiptap/react';
import axios from 'axios';
import tippy from 'tippy.js';
import MentionList from './MentionList.jsx';

const suggestion = {
  users: [],

  init: (projectId) => {
    axios.get(route('dropdown.values'), {params: {projectId, mentionProjectUsers: true}})
      .then(({data}) => {
        suggestion.users = data.mentionProjectUsers;
      })
      .catch((e) => console.error('Failed to fetch users for mention feature', e));
  },

  items: ({ query }) => {
    return suggestion.users
      .filter(item => item.toLowerCase().startsWith(query.toLowerCase()))
      .slice(0, 7);
  },

  render: () => {
    let component;
    let popup;

    return {
      onStart: props => {
        component = new ReactRenderer(MentionList, {
          props,
          editor: props.editor,
        });

        if (!props.clientRect) {
          return;
        }

        popup = tippy('body', {
          getReferenceClientRect: props.clientRect,
          appendTo: () => document.body,
          content: component.element,
          showOnCreate: true,
          interactive: true,
          trigger: 'manual',
          placement: 'bottom-start',
        });
      },

      onUpdate(props) {
        component.updateProps(props);

        if (!props.clientRect) {
          return;
        }

        popup[0].setProps({
          getReferenceClientRect: props.clientRect,
        })
      },

      onKeyDown(props) {
        if (props.event.key === 'Escape') {
          popup[0].hide();

          return true;
        }

        return component.ref?.onKeyDown(props);
      },

      onExit() {
        if(popup[0].popperInstance) popup[0].destroy();
        component.destroy();
      },
    }
  },
}

export default suggestion;
