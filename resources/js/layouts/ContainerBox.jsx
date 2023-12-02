import { Paper } from "@mantine/core";

export default function ContainerBox({ children, ...props }) {
  return (
    <Paper px={40} py={30} withBorder {...props}>
      {children}
    </Paper>
  );
}
