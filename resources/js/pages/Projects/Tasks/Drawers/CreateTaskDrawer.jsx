import { openConfirmModal } from "@/components/ConfirmModal";
import Dropzone from "@/components/Dropzone";
import RichTextEditor from "@/components/RichTextEditor";
import useTaskDrawerStore from "@/hooks/store/useTaskDrawerStore";
import useForm from "@/hooks/useForm";
import { hasRoles } from "@/utils/user";
import { usePage } from "@inertiajs/react";
import {
  Button,
  Checkbox,
  Drawer,
  Flex,
  MultiSelect,
  NumberInput,
  Select,
  Text,
  TextInput,
  rem,
} from "@mantine/core";
import { DateInput } from "@mantine/dates";
import { useEffect } from "react";
import LabelsDropdown from "./LabelsDropdown";
import classes from "./css/CreateTaskDrawer.module.css";

export function CreateTaskDrawer() {
  const { create, closeCreateTask } = useTaskDrawerStore();
  const {
    usersWithAccessToProject,
    taskGroups,
    labels,
    auth: { user },
  } = usePage().props;

  const [form, submit, updateValue] = useForm("post", route("users.store"), {
    group_id: create.group_id ? create.group_id.toString() : "",
    assigned_to_user_id: "",
    name: "",
    description: "",
    estimation: "",
    due_on: "",
    hidden_from_clients: false,
    billable: true,
    subscribers: [user.id.toString()],
    labels: [],
    attachments: [],
  });

  useEffect(() => {
    console.log(form.data);
  }, [form.data]);

  useEffect(() => {
    updateValue({
      group_id: create.group_id ? create.group_id.toString() : "",
      assigned_to_user_id: "",
      name: "",
      description: "",
      estimation: "",
      due_on: "",
      hidden_from_clients: false,
      billable: true,
      subscribers: [user.id.toString()],
      labels: [],
      attachments: [],
    });
  }, [create.opened]);

  const openCancelModal = () =>
    openConfirmModal({
      type: "danger",
      title: "Discard changes?",
      content: `All unsaved changes will be lost.`,
      confirmLabel: "Discard",
      confirmProps: { color: "red" },
      action: () => closeCreateTask(),
    });

  return (
    <Drawer
      opened={create.opened}
      onClose={closeCreateTask}
      title={
        <Text fz={rem(28)} fw={600} ml={25} my="sm" c="blue">
          Add new task
        </Text>
      }
      position="right"
      size={1000}
      overlayProps={{ backgroundOpacity: 0.55, blur: 3 }}
      transitionProps={{
        transition: "slide-left",
        duration: 400,
        timingFunction: "ease",
      }}
    >
      <form onSubmit={submit} className={classes.inner}>
        <div className={classes.content}>
          <TextInput
            label="Name"
            placeholder="Task name"
            required
            data-autofocus
            value={form.data.name}
            onChange={(e) => updateValue("name", e.target.value)}
            error={form.errors.name}
          />

          <RichTextEditor
            mt="xl"
            placeholder="Task description"
            onChange={(content) => updateValue("description", content)}
          />

          <Dropzone
            mt="xl"
            selected={form.data.attachments}
            onChange={(files) => updateValue("attachments", files)}
          />

          <MultiSelect
            label="Subscribers"
            placeholder="Select subscribers"
            searchable
            required
            mt="md"
            value={form.data.subscribers}
            onChange={(values) => updateValue("subscribers", values)}
            data={usersWithAccessToProject.map((i) => ({
              value: i.id.toString(),
              label: i.name,
            }))}
            error={form.errors.subscribers}
          />

          <Flex justify="space-between" mt="xl">
            <Button
              variant="transparent"
              w={100}
              loading={form.processing}
              onClick={openCancelModal}
            >
              Cancel
            </Button>

            <Button type="submit" w={120} loading={form.processing}>
              Add task
            </Button>
          </Flex>
        </div>
        <div className={classes.sidebar}>
          <Select
            label="Task group"
            placeholder="Select task group"
            required
            searchable
            value={form.data.group_id}
            onChange={(value) => updateValue("group_id", value)}
            data={taskGroups.map((i) => ({
              value: i.id.toString(),
              label: i.name,
            }))}
            error={form.errors.group_id}
          />

          <Select
            label="Assignee"
            placeholder="Select assignee"
            searchable
            mt="md"
            value={form.data.assigned_to_user_id}
            onChange={(value) => updateValue("assigned_to_user_id", value)}
            data={usersWithAccessToProject.map((i) => ({
              value: i.id.toString(),
              label: i.name,
            }))}
            error={form.errors.assigned_to_user_id}
          />

          <DateInput
            clearable
            valueFormat="DD MMM YYYY"
            minDate={new Date()}
            mt="md"
            label="Due date"
            placeholder="Pick task due date"
            value={form.data.due_on}
            onChange={(value) => updateValue("due_on", value)}
          />

          <LabelsDropdown
            items={labels}
            selected={form.data.labels}
            onChange={(values) => updateValue("labels", values)}
            mt="md"
          />

          <NumberInput
            label="Time estimation"
            mt="md"
            decimalScale={2}
            fixedDecimalScale
            defaultValue={0}
            min={0}
            allowNegative={false}
            step={0.5}
            suffix=" hours"
          />

          {!hasRoles(user, ["client"]) && (
            <Checkbox
              label="Hidden from clients"
              mt="xl"
              checked={form.data.hidden_from_clients}
              onChange={(event) =>
                updateValue("hidden_from_clients", event.currentTarget.checked)
              }
            />
          )}
        </div>
      </form>
    </Drawer>
  );
}
