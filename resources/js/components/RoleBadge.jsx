import RoleService from "@/services/RoleService";
import { Badge } from "@mantine/core";

export default function RoleBadge({ role }) {
  const color = new RoleService().getColor(role);

  return (
    <Badge color={color} variant="light">
      {role}
    </Badge>
  );
}
