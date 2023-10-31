import { Paper } from "@mantine/core";

export default function ContainerBox({ children, ...props }) {
  return (
    <Paper px={45} py={35} withBorder {...props}>
      {children}
    </Paper>
  );
}
