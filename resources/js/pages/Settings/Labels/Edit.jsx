import ActionButton from "@/components/ActionButton";
import BackButton from "@/components/BackButton";
import useForm from "@/hooks/useForm";
import ContainerBox from "@/layouts/ContainerBox";
import Layout from "@/layouts/MainLayout";
import { redirectTo } from "@/utils/route";
import { usePage } from "@inertiajs/react";
import { Anchor, Breadcrumbs, ColorInput, Grid, Group, TextInput, Title } from "@mantine/core";

const LabelEdit = () => {
  const { item } = usePage().props;

  const [form, submit, updateValue] = useForm("post", route("settings.labels.update", item.id), {
    _method: "put",
    name: item.name,
    color: item.color || "",
  });

  return (
    <>
      <Breadcrumbs fz={14} mb={30}>
        <Anchor href="#" onClick={() => redirectTo("settings.labels.index")} fz={14}>
          Labels
        </Anchor>
        <div>Edit</div>
      </Breadcrumbs>

      <Grid justify="space-between" align="flex-end" gutter="xl" mb="lg">
        <Grid.Col span="auto">
          <Title order={1}>Edit label</Title>
        </Grid.Col>
        <Grid.Col span="content"></Grid.Col>
      </Grid>

      <ContainerBox maw={400}>
        <form onSubmit={submit}>
          <TextInput
            label="Name"
            placeholder="Label name"
            required
            value={form.data.name}
            onChange={(e) => updateValue("name", e.target.value)}
            error={form.errors.name}
          />
          <ColorInput
            label="Color"
            placeholder="Label color"
            required
            mt="md"
            swatches={[
              "#343A40",
              "#E03231",
              "#C2255C",
              "#9C36B5",
              "#6741D9",
              "#3B5BDB",
              "#2771C2",
              "#2A8599",
              "#2B9267",
              "#309E44",
              "#66A810",
              "#F08C00",
              "#E7590D",
            ]}
            swatchesPerRow={7}
            value={form.data.color}
            onChange={(color) => updateValue("color", color)}
            error={form.errors.color}
          />

          <Group justify="space-between" mt="xl">
            <BackButton route="settings.labels.index" />
            <ActionButton loading={form.processing}>Update</ActionButton>
          </Group>
        </form>
      </ContainerBox>
    </>
  );
};

LabelEdit.layout = (page) => <Layout title="Edit user">{page}</Layout>;

export default LabelEdit;
