import { openConfirmModal } from "@/components/ConfirmModal";
import Dropzone from "@/components/Dropzone";
import RichTextEditor from "@/components/RichTextEditor";
import useTaskDrawerStore from "@/hooks/store/useTaskDrawerStore";
import useTasksStore from "@/hooks/store/useTasksStore";
import useForm from "@/hooks/useForm";
import { date } from "@/utils/date";
import { openAttachment } from "@/utils/task";
import { hasRoles } from "@/utils/user";
import { usePage } from "@inertiajs/react";
import {
  Breadcrumbs,
  Button,
  Checkbox,
  Drawer,
  Flex,
  Group,
  MultiSelect,
  NumberInput,
  Select,
  Text,
  TextInput,
  rem,
} from "@mantine/core";
import { DateInput } from "@mantine/dates";
import dayjs from "dayjs";
import { useEffect } from "react";
import LabelsDropdown from "./LabelsDropdown";
import Timer from "./Timer";
import classes from "./css/TaskDrawer.module.css";

export function EditTaskDrawer() {
  const { edit, closeEditTask } = useTaskDrawerStore();
  const { findTask, complete, deleteAttachment, uploadAttachments } =
    useTasksStore();
  const {
    usersWithAccessToProject,
    taskGroups,
    labels,
    auth: { user },
  } = usePage().props;

  const task = findTask(edit.task.id);

  const initial = {
    group_id: task?.group_id || "",
    assigned_to_user_id: task?.assigned_to_user_id || "",
    name: task?.name || "",
    description: task?.description || "",
    estimation: task?.estimation || "",
    due_on: task?.due_on ? dayjs(task?.due_on) : "",
    hidden_from_clients: task?.hidden_from_clients || false,
    billable: task?.billable || true,
    subscribers: (task?.subscribed_users || []).map((i) => i.id.toString()),
    labels: (task?.labels || []).map((i) => i.id),
  };

  const [form, submit, updateValue] = useForm(
    "post",
    route("projects.tasks.update", [task?.project_id || 0, task?.id || 0]),
    {
      _method: "put",
      ...initial,
    },
  );

  useEffect(() => {
    if (edit.opened) {
      updateValue({ ...initial });
    }
  }, [edit.opened]);

  const closeDrawer = (force = false) => {
    if (
      force ||
      (JSON.stringify(form.data) === JSON.stringify(initial) &&
        !form.processing)
    ) {
      closeEditTask();
    } else {
      openConfirmModal({
        type: "danger",
        title: "Discard changes?",
        content: `All unsaved changes will be lost.`,
        confirmLabel: "Discard",
        confirmProps: { color: "red" },
        action: () => closeEditTask(),
      });
    }
  };

  const confirmDeleteAttachment = (index) => {
    openConfirmModal({
      type: "danger",
      title: "Delete attachment",
      content: `Are you sure you want to delete this attachment?`,
      confirmLabel: "Delete",
      confirmProps: { color: "red" },
      action: () => deleteAttachment(task, index),
    });
  };

  return task ? (
    <Drawer
      opened={edit.opened}
      onClose={closeDrawer}
      title={
        <Group ml={25} my="sm">
          <Checkbox
            size="md"
            radius="xl"
            color="green"
            checked={task.completed_at !== null}
            onChange={(e) => complete(task, e.currentTarget.checked)}
            className={
              can("complete task") ? classes.checkbox : classes.disabledCheckbox
            }
          />
          <Text
            fz={rem(28)}
            fw={600}
            td={task.completed_at !== null ? "line-through" : null}
            truncate="end"
          >
            #{task.number}: {task.name}
          </Text>
        </Group>
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
      <Breadcrumbs
        c="dimmed"
        ml={24}
        mb="xs"
        separator="I"
        separatorMargin="sm"
      >
        <Text size="xs">{task.project.name}</Text>
        <Text size="xs">Task #{task.number}</Text>
        <Text size="xs">
          Created by {task.created_by_user.name} on {date(task.created_at)}
        </Text>
      </Breadcrumbs>
      <form
        onSubmit={(event) =>
          submit(event, { onSuccess: () => closeDrawer(true) })
        }
        className={classes.inner}
      >
        <div className={classes.content}>
          <TextInput
            label="Name"
            placeholder="Task name"
            required
            value={form.data.name}
            onChange={(e) => updateValue("name", e.target.value)}
            error={form.errors.name}
          />

          <RichTextEditor
            mt="xl"
            placeholder="Task description"
            content={form.data.description}
            onChange={(content) => updateValue("description", content)}
          />

          <Dropzone
            mt="xl"
            selected={task.attachments}
            onChange={(files) => uploadAttachments(task, files)}
            remove={(index) => confirmDeleteAttachment(index)}
            open={(file) => openAttachment(file)}
          />

          <Flex justify="space-between" mt="xl">
            <Button
              variant="transparent"
              w={100}
              disabled={form.processing}
              onClick={closeDrawer}
            >
              Cancel
            </Button>

            <Button type="submit" w={120} loading={form.processing}>
              Save
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
            value={form.data.estimation}
            min={0}
            allowNegative={false}
            step={0.5}
            suffix=" hours"
            onChange={(value) => updateValue("estimation", value)}
          />

          <Timer mt="xl" task={task} />

          <Checkbox
            label="Billable"
            mt="xl"
            checked={form.data.billable}
            onChange={(event) =>
              updateValue("billable", event.currentTarget.checked)
            }
          />

          {!hasRoles(user, ["client"]) && (
            <Checkbox
              label="Hidden from clients"
              mt="md"
              checked={form.data.hidden_from_clients}
              onChange={(event) =>
                updateValue("hidden_from_clients", event.currentTarget.checked)
              }
            />
          )}

          <MultiSelect
            label="Subscribers"
            placeholder={
              !form.data.subscribers.length ? "Select subscribers" : null
            }
            mt="lg"
            value={form.data.subscribers}
            onChange={(values) => updateValue("subscribers", values)}
            data={usersWithAccessToProject.map((i) => ({
              value: i.id.toString(),
              label: i.name,
            }))}
            error={form.errors.subscribers}
          />
        </div>
      </form>
    </Drawer>
  ) : (
    <></>
  );
}
