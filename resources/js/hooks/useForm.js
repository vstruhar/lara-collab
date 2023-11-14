import { useScrollIntoView } from "@mantine/hooks";
import { useForm as usePrecognitionForm } from "laravel-precognition-react-inertia";
import { isObject } from "lodash";


const useForm = (method, url, data) => {
    const form = usePrecognitionForm(method, url, data);

    const { scrollIntoView } = useScrollIntoView({ duration: 1000 });

    const submit = (e, props) => {
      e.preventDefault();

      form.submit({
        forceFormData: true,
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
      } else {
        form.setData(field, value);
        form.forgetError(field);
      }
    };

    return [form, submit, updateValue];
  };

  export default useForm;
