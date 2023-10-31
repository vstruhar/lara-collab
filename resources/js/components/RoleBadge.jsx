import useRoles from "@/hooks/useRoles";
import { Badge } from "@mantine/core";

export default function RoleBadge({ role }) {
  const { getColor } = useRoles();

  return (
    <Badge color={getColor(role)} variant="light">
      {role}
    </Badge>
  );
}
