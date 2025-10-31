export default function HeadingXSmall({
    title,
    description,
}: {
    title: string;
    description?: string;
}) {
    return (
        <header>
            <h3 className="mb-0.5 text-sm font-semibold">{title}</h3>
            {description && (
                <p className="text-xs text-muted-foreground">{description}</p>
            )}
        </header>
    );
}
