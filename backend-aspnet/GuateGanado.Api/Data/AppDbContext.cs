using GuateGanado.Api.Models;
using Microsoft.EntityFrameworkCore;

namespace GuateGanado.Api.Data;

public class AppDbContext : DbContext
{
    public AppDbContext(DbContextOptions<AppDbContext> options) : base(options) { }

    public DbSet<Animal> Animales => Set<Animal>();
    public DbSet<Potrero> Potreros => Set<Potrero>();
    public DbSet<Usuario> Usuarios => Set<Usuario>();

    protected override void OnModelCreating(ModelBuilder b)
    {
        b.Entity<Animal>()
            .HasIndex(a => a.NumeroArete)
            .IsUnique();

        // Restrict => al borrar un potrero con animales se produce el 409 del contrato.
        b.Entity<Animal>()
            .HasOne(a => a.Potrero)
            .WithMany(p => p.Animales)
            .HasForeignKey(a => a.PotreroId)
            .OnDelete(DeleteBehavior.Restrict);

        b.Entity<Usuario>()
            .HasIndex(u => u.Email)
            .IsUnique();
    }
}
