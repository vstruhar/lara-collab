import { useForm as useInertiaForm } from '@inertiajs/react';
import { useScrollIntoView } from '@mantine/hooks';
import { isObject } from 'lodash';

export default function useForm(method, url, data) {
  const form = useInertiaForm(method, url, data ?? {});

  const { scrollIntoView } = useScrollIntoView({ duration: 1000 });

  const submit = (e, props) => {
    e.preventDefault();

    form.submit({
      preserveScroll: false,
      onError: () => {
        scrollIntoView({
          target: document.querySelector('[data-error="true"]'),
        });
      },

      ...props,
    });
  };

  const updateValue = (field, value) => {
    if (isObject(field)) {
      form.setData(field);
      form.clearErrors();
      return;
    }

    // Inertia's setData(key, value) deep-clones the whole form. Unchanged arrays
    // then look new, and effects that watch them run again and reset the form.
    form.setData(current => ({
      ...current,
      [field]: value,
    }));
    form.clearErrors(field);
  };

  return [form, submit, updateValue];
}
