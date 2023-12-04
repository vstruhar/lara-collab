import ActionButton from "@/components/ActionButton";
import BackButton from "@/components/BackButton";
import useForm from "@/hooks/useForm";
import ContainerBox from "@/layouts/ContainerBox";
import Layout from "@/layouts/MainLayout";
import { redirectTo } from "@/utils/route";
import { usePage } from "@inertiajs/react";
import {
  Anchor,
  Breadcrumbs,
  Grid,
  Group,
  MultiSelect,
  NumberInput,
  Radio,
  Select,
  SimpleGrid,
  Textarea,
  Title,
} from "@mantine/core";
import { useDidUpdate } from "@mantine/hooks";
import { useState } from "react";

const InvoiceCreate = () => {
  const { projects, clientCompanies } = usePage().props;
  const [filteredProjects, setFilteredProjects] = useState([]);
  const [currency, setCurrency] = useState("");

  const [form, submit, updateValue] = useForm("post", route("invoices.store"), {
    client_company_id: "",
    projects: [],
    type: "hourly",
    hourly_rate: 0,
    fixed_amount: 0,
    note: "",
  });

  useDidUpdate(() => {
    setFilteredProjects(
      projects
        .filter((i) => i.client_company_id == form.data.client_company_id)
        .map((i) => ({ value: i.id.toString(), label: i.name })),
    );

    const company = clientCompanies.find((i) => form.data.client_company_id === i.id.toString());

    if (company.rate) {
      updateValue("hourly_rate", company.rate / 100);
    }
    setCurrency(company.currency.symbol);
  }, [form.data.client_company_id]);

  return (
    <>
      <Breadcrumbs fz={14} mb={30}>
        <Anchor href="#" onClick={() => redirectTo("invoices.index")} fz={14}>
          Invoices
        </Anchor>
        <div>Create</div>
      </Breadcrumbs>

      <Grid justify="space-between" align="flex-end" gutter="xl" mb="lg">
        <Grid.Col span="auto">
          <Title order={1}>Create invoice</Title>
        </Grid.Col>
        <Grid.Col span="content"></Grid.Col>
      </Grid>

      <SimpleGrid cols={2}>
        <ContainerBox>
          <form onSubmit={submit}>
            <Select
              label="Client company"
              placeholder="Select client company"
              searchable={true}
              required
              value={form.data.client_company_id}
              onChange={(value) => updateValue("client_company_id", value)}
              data={clientCompanies.map((i) => ({ value: i.id.toString(), label: i.name }))}
              error={form.errors.client_company_id}
            />

            <MultiSelect
              label="Projects"
              placeholder={
                filteredProjects.length ? "Select projects" : "Please select client company"
              }
              readOnly={filteredProjects.length === 0}
              withAsterisk
              mt="md"
              value={form.data.projects}
              onChange={(values) => updateValue("projects", values)}
              data={filteredProjects}
              error={form.errors.projects}
            />

            <Radio.Group
              label="Invoice type"
              mt="md"
              withAsterisk
              value={form.data.type}
              onChange={(value) => updateValue("type", value)}
            >
              <Group mt="xs">
                <Radio value="hourly" label="Hourly" />
                <Radio value="fixed_amount" label="Fixed amount" />
              </Group>
            </Radio.Group>

            {form.data.type === "hourly" && (
              <NumberInput
                label="Hourly rate"
                mt="md"
                allowNegative={false}
                clampBehavior="strict"
                decimalScale={2}
                fixedDecimalScale={true}
                prefix={currency}
                value={form.data.hourly_rate}
                onChange={(value) => updateValue("hourly_rate", value)}
                error={form.errors.hourly_rate}
              />
            )}

            {form.data.type === "fixed_amount" && (
              <NumberInput
                label="Fixed amount"
                mt="md"
                allowNegative={false}
                clampBehavior="strict"
                decimalScale={2}
                fixedDecimalScale={true}
                prefix={currency}
                value={form.data.fixed_amount}
                onChange={(value) => updateValue("fixed_amount", value)}
                error={form.errors.fixed_amount}
              />
            )}

            <Textarea
              label="Note"
              placeholder="Invoice note"
              mt="md"
              autosize
              minRows={4}
              maxRows={8}
              value={form.data.note}
              onChange={(e) => updateValue("note", e.target.value)}
            />

            <Group justify="space-between" mt="xl">
              <BackButton route="invoices.index" />
              <ActionButton loading={form.processing}>Create</ActionButton>
            </Group>
          </form>
        </ContainerBox>
        <ContainerBox>2</ContainerBox>
      </SimpleGrid>
    </>
  );
};

InvoiceCreate.layout = (page) => <Layout title="Create invoice">{page}</Layout>;

export default InvoiceCreate;
