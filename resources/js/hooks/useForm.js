import { useScrollIntoView } from "@mantine/hooks";
import { useForm as usePrecognitionForm } from "laravel-precognition-react-inertia";


const useForm = (method, url, fields) => {
    const form = usePrecognitionForm(method, url, fields);

    const { scrollIntoView } = useScrollIntoView({ duration: 1000 });

    const submit = (e) => {
      e.preventDefault();

      form.submit({
        forceFormData: true,
        preserveScroll: true,
        onError: () => {
          scrollIntoView({
            target: document.querySelector('[data-error="true"]'),
          });
        },
      });
    };

    const updateValue = (field, value) => {
      form.setData(field, value);
      form.forgetError(field);
    };

    return [form, submit, updateValue];
  };

  export default useForm;
