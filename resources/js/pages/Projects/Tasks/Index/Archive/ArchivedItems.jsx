import { EmptyResult } from "@/components/EmptyResult";
import { Text } from "@mantine/core";
import ArchivedTask from "./ArchivedTask";
import ArchivedTaskGroup from "./ArchivedTaskGroup";

export default function ArchivedItems({ groups, tasks }) {
  const hasTasks = Object.keys(tasks).some((key) => tasks[key].length > 0);

  return groups.length || hasTasks ? (
    <>
      {hasTasks && (
        <>
          <Text fz={24} fw={600} mb={20}>
            Tasks
          </Text>
          {Object.keys(tasks).map((key) =>
            tasks[key].map((task) => <ArchivedTask key={`task-${task.id}`} task={task} />),
          )}
        </>
      )}
      {groups.length > 0 && (
        <>
          <Text fz={24} fw={600} mt={35} mb={20}>
            Task groups
          </Text>
          {groups.map((group) => (
            <ArchivedTaskGroup key={`group-${group.id}`} group={group} />
          ))}
        </>
      )}
    </>
  ) : (
    <EmptyResult title="No tasks or groups found" subtitle="or none match your search criteria" />
  );
}
