export function renderLabelWithAsterisk(label, required) {
  if (!label) return null;
  if (typeof label !== 'string') {
    return (
      <>
        {label}
        {required && <span className="text-red-500 font-bold ml-1">*</span>}
      </>
    );
  }

  if (label.includes('*')) {
    const parts = label.split('*');
    return (
      <>
        {parts[0]}
        <span className="text-red-500 font-bold ml-0.5">*</span>
        {parts.slice(1).join('*')}
      </>
    );
  }

  return (
    <>
      {label}
      {required && <span className="text-red-500 font-bold ml-1">*</span>}
    </>
  );
}

export default function Input({ label, id, required, type = "text", error, className = "", inputClassName = "", ...props }) {
  return (
    <div className={`mb-5 ${className}`}>
      {label && (
        <label htmlFor={id} className="block mb-2 font-medium text-textMain">
          {renderLabelWithAsterisk(label, required)}
        </label>
      )}
      <input
        id={id}
        type={type}
        className={`w-full px-4 py-3 border rounded-md font-sarabun text-[15px] transition-colors focus:outline-none focus:border-primary focus:ring-4 focus:ring-primary/10 ${
          error ? "border-error bg-red-50" : "border-border bg-white"
        } ${inputClassName}`}
        {...props}
      />
      {error && <p className="mt-1 text-sm text-error">{error}</p>}
    </div>
  );
}
