import { Head } from "@inertiajs/react";
import { Container } from "@mantine/core";

export default function GuestLayout({ title, children }) {
  return (
    <>
      <Head title={title} />

      <Container size={440} my={80}>
        {children}
      </Container>
    </>
  );
}
